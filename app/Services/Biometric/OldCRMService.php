<?php

namespace App\Services\Biometric;

use App\Models\BiometricMachine;
use App\Models\Employee;
use App\Models\Attendance;
use App\Services\Biometric\Contracts\BiometricProviderInterface;
use App\Helpers\Helper;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;

class OldCRMService implements BiometricProviderInterface
{
    protected ?BiometricMachine $machine = null;

    /**
     * Get the provider name
     */
    public function getProviderName(): string
    {
        return 'Old CRM';
    }

    /**
     * Get the integration type (push or pull)
     */
    public function getIntegrationType(): string
    {
        return 'pull';
    }

    /**
     * Set the biometric machine instance
     */
    public function setMachine(BiometricMachine $machine): self
    {
        $this->machine = $machine;
        return $this;
    }

    /**
     * Get API base URL
     */
    protected function getApiUrl(): string
    {
        return $this->machine->api_url ?? '';
    }

    /**
     * Get HTTP client with appropriate authentication
     */
    protected function getHttpClient()
    {
        $authType = $this->machine->auth_type ?? 'basic';
        $client = Http::timeout(60);

        if ($authType === 'basic') {
            $client = $client->withBasicAuth(
                $this->machine->api_username ?? '',
                $this->machine->api_password ?? ''
            );
        } elseif ($authType === 'bearer_token') {
            $client = $client->withToken($this->machine->bearer_token ?? '');
        } elseif ($authType === 'api_key') {
            $keyName = $this->machine->api_key_name ?? 'X-API-Key';
            $keyValue = $this->machine->api_key_value ?? '';
            $client = $client->withHeaders([
                $keyName => $keyValue,
            ]);
        }

        return $client;
    }

    /**
     * Test connection to Old CRM API
     */
    public function testConnection(): array
    {
        try {
            if (empty($this->getApiUrl())) {
                throw new Exception('API URL is required');
            }

            $response = $this->getHttpClient()->get($this->getApiUrl());

            if (!$response->successful()) {
                throw new Exception('Connection failed: ' . $response->status());
            }

            return [
                'success' => true,
                'message' => 'Connection successful',
            ];
        } catch (Exception $e) {
            Log::error('Old CRM connection test failed', [
                'machine_id' => $this->machine->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Fetch attendance records from Old CRM API
     */
    public function fetchAttendance(Carbon $startDate, Carbon $endDate): array
    {
        if (!$this->machine) {
            throw new Exception('Biometric machine not set');
        }

        $url = $this->getApiUrl();
        if (empty($url)) {
            throw new Exception('API URL is required for Old CRM sync');
        }

        Log::info('Old CRM API Request', [
            'machine_id' => $this->machine->id,
            'url' => $url,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
        ]);

        $response = $this->getHttpClient()->get($url, [
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
        ]);

        if (!$response->successful()) {
            throw new Exception('Failed to fetch attendance from Old CRM: ' . $response->status());
        }

        $data = $response->json();

        // Handle different possible response structures
        $records = $data['data'] ?? $data['records'] ?? $data ?? [];

        if (!is_array($records)) {
            Log::warning('Old CRM API returned non-array data', ['data' => $data]);
            return [];
        }

        // Transform and store
        $transformed = $this->transformRecords($records);
        $storedCount = $this->storeAttendanceRecords($transformed);

        return [
            'records_fetched' => count($records),
            'records_transformed' => count($transformed),
            'records_stored' => $storedCount,
            'data' => $transformed,
        ];
    }

    /**
     * Transform records to standard format
     */
    protected function transformRecords(array $records): array
    {
        $transformed = [];

        foreach ($records as $record) {
            try {
                // Try to find employee identifier
                $empCode = $record['employee_code'] ?? $record['emp_code'] ?? $record['biometric_id'] ?? $record['user_id'] ?? null;
                $timestamp = $record['timestamp'] ?? $record['punch_time'] ?? $record['datetime'] ?? $record['date_time'] ?? null;

                if (empty($empCode) || empty($timestamp)) {
                    continue;
                }

                $dt = Carbon::parse($timestamp);

                $transformed[] = [
                    'employee_code' => $empCode,
                    'timestamp' => $dt->toDateTimeString(),
                    'attendance_date' => $dt->format('Y-m-d'),
                    'punch_in_time' => $dt->format('H:i:s'),
                    'attendace_type' => strtolower($record['type'] ?? $record['mode'] ?? 'in'),
                    'raw_data' => $record,
                ];
            } catch (\Exception $e) {
                Log::warning('Failed to transform Old CRM record', [
                    'record' => $record,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $transformed;
    }

    /**
     * Store attendance records in database
     */
    protected function storeAttendanceRecords(array $records): int
    {
        if (empty($records)) {
            return 0;
        }

        $companyId = $this->machine->company_id;
        $storedCount = 0;

        // Group records by employee code to process them together
        $groupedByEmployee = [];
        foreach ($records as $record) {
            $empCode = $record['employee_code'];
            if (!isset($groupedByEmployee[$empCode])) {
                $groupedByEmployee[$empCode] = [];
            }
            $groupedByEmployee[$empCode][] = $record;
        }

        foreach ($groupedByEmployee as $empCode => $employeeRecords) {
            try {
                // 1. Find employee (Direct match or flexible matching)
                $employee = Employee::where('company_id', $companyId)
                    ->where(function ($q) use ($empCode) {
                        $q->where('biometric_user_id', $empCode)
                            ->orWhere('employee_code', $empCode);
                    })
                    ->first();

                // 2. Flexible matching (numeric handling) if not found
                if (!$employee && is_numeric($empCode)) {
                    $numericCode = (int) $empCode;
                    $employee = Employee::where('company_id', $companyId)
                        ->where(function ($q) use ($empCode, $numericCode) {
                            $q->whereRaw('CAST(biometric_user_id AS UNSIGNED) = ?', [$numericCode])
                                ->orWhereRaw('CAST(employee_code AS UNSIGNED) = ?', [$numericCode])
                                ->orWhere('biometric_user_id', 'LIKE', '%' . $empCode)
                                ->orWhere('employee_code', 'LIKE', '%' . $empCode);
                        })
                        ->first();
                }

                if (!$employee) {
                    Log::warning('Old CRM Sync: Employee not found', [
                        'company_id' => $companyId,
                        'searched_code' => $empCode
                    ]);
                    continue;
                }

                // Process each record for this employee
                foreach ($employeeRecords as $record) {
                    $attendanceDate = $record['attendance_date'];
                    $punchTime = $record['punch_in_time'];
                    $type = in_array($record['attendace_type'], ['in', 'out']) ? $record['attendace_type'] : 'in';

                    // Duplicate check: check if this specific punch already exists
                    $exists = Attendance::where('employee_id', $employee->id)
                        ->where('attendance_date', $attendanceDate)
                        ->where('punch_in_time', $punchTime)
                        ->where('attendace_type', $type)
                        ->exists();

                    if ($exists) {
                        continue;
                    }

                    Attendance::create([
                        'company_id' => $companyId,
                        'employee_id' => $employee->id,
                        'shift_id' => $employee->employmentDetail?->shift ?? 0,
                        'attendance_date' => $attendanceDate,
                        'create_date' => now()->toDateString(),
                        'punch_in_time' => $punchTime,
                        'attendace_type' => $type,
                        'remark' => 'Synced from Old CRM',
                        'status' => 'active',
                        'records_source' => 'old_crm',
                        'requested_data' => json_encode($record['raw_data']),
                        'device_serial' => $this->machine->serial_number,
                        'device_ip' => $this->machine->ip_address,
                        'created_by' => $this->machine->created_by,
                    ]);

                    $storedCount++;
                }
            } catch (\Exception $e) {
                Log::error('Failed to process Old CRM records for employee', [
                    'employee_code' => $empCode,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $storedCount;
    }

    public function authenticate(): array
    {
        return ['authenticated' => true];
    }

    public function syncEmployees(): array
    {
        return [];
    }

    /**
     * Push salary data to Old CRM API
     */
    public function pushSalaryData(array $salaryData): array
    {
        if (!$this->machine) {
            throw new Exception('Biometric machine not set for Old CRM push');
        }

        $url = $this->getApiUrl();
        if (empty($url)) {
            throw new Exception('API URL is required for Old CRM push');
        }

        // Logic to determine if we need to append a specific endpoint
        // If the URL is just a base domain/path, we might need to append /salary or similar
        // Determine the correct push URL
        $pushUrl = $url;

        // If the URL points to attendance-logs, switch to salary-logs for this push
        if (str_contains($url, 'attendance-logs')) {
            $pushUrl = str_replace('attendance-logs', 'salary-logs', $url);
        } elseif (str_contains($url, 'attendance_logs.php')) {
            $pushUrl = str_replace('attendance_logs.php', 'salary_logs.php', $url);
        }

        Log::info('Pushing Salary Data to Old CRM', [
            'machine_id' => $this->machine->id,
            'url' => $pushUrl,
            'employee_code' => $salaryData['employee_code'] ?? null,
        ]);

        try {
            // Using POST as form data for pushing data to legacy PHP endpoints
            $response = $this->getHttpClient()->asForm()->post($pushUrl, $salaryData);

            if (!$response->successful()) {
                Log::error('Failed to push salary to Old CRM', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'url' => $pushUrl
                ]);

                return [
                    'success' => false,
                    'message' => 'Failed to push salary to Old CRM: ' . $response->status(),
                ];
            }

            return [
                'success' => true,
                'message' => 'Salary pushed successfully',
                'data' => $response->json(),
            ];
        } catch (Exception $e) {
            Log::error('Old CRM salary push failed', [
                'machine_id' => $this->machine->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Push failed: ' . $e->getMessage(),
            ];
        }
    }
}
