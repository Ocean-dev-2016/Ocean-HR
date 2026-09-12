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

class ETimeOfficeService implements BiometricProviderInterface
{
    protected ?BiometricMachine $machine = null;

    /**
     * Get the provider name
     */
    public function getProviderName(): string
    {
        return 'eTimeOffice';
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
        $baseUrl = $this->machine->api_url ?? 'https://api.etimeoffice.com/api/';
        // Ensure URL ends with /api/ or /api
        if (!str_ends_with($baseUrl, '/api') && !str_ends_with($baseUrl, '/api/')) {
            $baseUrl = rtrim($baseUrl, '/') . '/api/';
        } elseif (str_ends_with($baseUrl, '/api')) {
            $baseUrl .= '/';
        }
        return $baseUrl;
    }

    /**
     * Build authentication header based on auth type
     */
    protected function buildAuthHeader(): string
    {
        $authType = $this->machine->auth_type ?? 'basic';

        if ($authType === 'basic') {
            // eTimeOffice Basic Auth format: base64(corporateid:username:password:true)
            $corporateId = $this->machine->corporate_id ?? '';
            $username = $this->machine->api_username ?? '';
            $password = $this->machine->api_password ?? '';

            $authString = "{$corporateId}:{$username}:{$password}:true";
            return base64_encode($authString);
        } elseif ($authType === 'bearer_token') {
            return $this->machine->bearer_token ?? '';
        } elseif ($authType === 'api_key') {
            // For API Key, return the key value (header will be set separately)
            return $this->machine->api_key_value ?? '';
        }

        return '';
    }

    /**
     * Get HTTP client with appropriate authentication
     */
    protected function getHttpClient()
    {
        $authType = $this->machine->auth_type ?? 'basic';
        $client = Http::timeout(60);

        if ($authType === 'basic') {
            $client = $client->withHeaders([
                'Authorization' => 'Basic ' . $this->buildAuthHeader(),
            ]);
        } elseif ($authType === 'bearer_token') {
            $client = $client->withToken($this->machine->bearer_token ?? '');
        } elseif ($authType === 'api_key') {
            $keyName = $this->machine->api_key_name ?? 'X-API-Key';
            $keyValue = $this->machine->api_key_value ?? '';
            $client = $client->withHeaders([
                $keyName => $keyValue,
            ]);
        } elseif ($authType === 'custom') {
            $customHeaders = $this->machine->custom_headers ?? [];
            if (is_string($customHeaders)) {
                $customHeaders = json_decode($customHeaders, true) ?? [];
            }
            $client = $client->withHeaders($customHeaders);
        }

        return $client;
    }

    /**
     * Authenticate with eTimeOffice API
     * Note: eTimeOffice uses Basic Auth by default, but supports other auth types
     */
    public function authenticate(): array
    {
        if (!$this->machine) {
            throw new Exception('Biometric machine not set');
        }

        // Validate required fields based on auth type
        $authType = $this->machine->auth_type ?? 'basic';

        if ($authType === 'basic') {
            // eTimeOffice Basic Auth requires corporate_id and username
            if (empty($this->machine->corporate_id) || empty($this->machine->api_username)) {
                throw new Exception('Corporate ID and Username are required for Basic Auth');
            }
            // Password is optional (can be set later)
        } elseif ($authType === 'bearer_token') {
            if (empty($this->machine->bearer_token)) {
                throw new Exception('Bearer Token is required');
            }
        } elseif ($authType === 'api_key') {
            if (empty($this->machine->api_key_name) || empty($this->machine->getRawApiKeyValueAttribute())) {
                throw new Exception('API Key Name and Value are required');
            }
        } elseif ($authType === 'custom') {
            $customHeaders = $this->machine->custom_headers ?? [];
            if (empty($customHeaders)) {
                throw new Exception('Custom Headers are required');
            }
        }

        // Test authentication by making a simple API call
        // Using DownloadPunchData with a small date range to test
        $testDate = Carbon::now()->format('d/m/Y_H:i');
        $testUrl = $this->getApiUrl() . 'DownloadPunchData';

        $response = $this->getHttpClient()->get($testUrl, [
            'Empcode' => 'ALL',
            'FromDate' => $testDate,
            'ToDate' => $testDate,
        ]);

        if (!$response->successful()) {
            throw new Exception('Authentication failed: ' . $response->body() . ' (Status: ' . $response->status() . ')');
        }

        return [
            'authenticated' => true,
            'message' => 'Authentication successful',
        ];
    }

    /**
     * Test connection to eTimeOffice API
     */
    public function testConnection(): array
    {
        try {
            $this->authenticate();
            return [
                'success' => true,
                'message' => 'Connection successful',
            ];
        } catch (Exception $e) {
            Log::error('eTimeOffice connection test failed', [
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
     * Fetch attendance records from eTimeOffice API
     * Uses DownloadPunchData endpoint which provides raw punch data
     */
    public function fetchAttendance(Carbon $startDate, Carbon $endDate): array
    {
        if (!$this->machine) {
            throw new Exception('Biometric machine not set');
        }

        // Validate required fields based on auth type
        $authType = $this->machine->auth_type ?? 'basic';

        if ($authType === 'basic') {
            // eTimeOffice Basic Auth requires corporate_id and username
            if (empty($this->machine->corporate_id) || empty($this->machine->api_username)) {
                throw new Exception('Corporate ID and Username are required for Basic Auth');
            }
            // Password is optional (can be set later)
        } elseif ($authType === 'bearer_token') {
            if (empty($this->machine->bearer_token)) {
                throw new Exception('Bearer Token is required');
            }
        } elseif ($authType === 'api_key') {
            if (empty($this->machine->api_key_name) || empty($this->machine->getRawApiKeyValueAttribute())) {
                throw new Exception('API Key Name and Value are required');
            }
        } elseif ($authType === 'custom') {
            $customHeaders = $this->machine->custom_headers ?? [];
            if (empty($customHeaders)) {
                throw new Exception('Custom Headers are required');
            }
        }

        // Format dates as per eTimeOffice API: dd/MM/yyyy_HH:mm
        $fromDate = $startDate->format('d/m/Y_H:i');
        $toDate = $endDate->format('d/m/Y_H:i');

        // Use DownloadPunchData endpoint
        $url = $this->getApiUrl() . 'DownloadPunchData';

        Log::info('eTimeOffice API Request', [
            'machine_id' => $this->machine->id,
            'url' => $url,
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'auth_type' => $this->machine->auth_type ?? 'basic',
        ]);

        $response = $this->getHttpClient()->get($url, [
            'Empcode' => 'ALL', // Fetch all employees
            'FromDate' => $fromDate,
            'ToDate' => $toDate,
        ]);

        if (!$response->successful()) {
            $errorMsg = 'Failed to fetch attendance: ' . $response->body() . ' (Status: ' . $response->status() . ')';
            Log::error('eTimeOffice API Error', [
                'machine_id' => $this->machine->id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new Exception($errorMsg);
        }

        // Parse response
        $data = $response->body();

        // eTimeOffice DownloadPunchData API returns JSON format
        if (!$this->isJson($data)) {
            throw new Exception('Invalid response format. Expected JSON but received: ' . substr($data, 0, 100));
        }

        $jsonData = json_decode($data, true);

        // Check for API errors
        if (isset($jsonData['Error']) && $jsonData['Error'] === true) {
            $errorMsg = $jsonData['Msg'] ?? 'Unknown error from eTimeOffice API';
            Log::error('eTimeOffice API Error Response', [
                'machine_id' => $this->machine->id,
                'error' => $errorMsg,
                'response' => $jsonData,
            ]);
            throw new Exception('eTimeOffice API Error: ' . $errorMsg);
        }

        // Extract PunchData array from response
        $punchData = $jsonData['PunchData'] ?? [];

        if (empty($punchData) || !is_array($punchData)) {
            Log::info('eTimeOffice API returned empty PunchData', [
                'machine_id' => $this->machine->id,
                'response' => $jsonData,
            ]);
            return [
                'records_fetched' => 0,
                'records_transformed' => 0,
                'records_stored' => 0,
                'data' => [],
            ];
        }

        // Transform records to standard format
        $transformedRecords = $this->transformRecords($punchData);

        // Store attendance records in database
        $storedCount = $this->storeAttendanceRecords($transformedRecords);

        Log::info('eTimeOffice attendance sync completed', [
            'machine_id' => $this->machine->id,
            'records_fetched' => count($punchData),
            'records_transformed' => count($transformedRecords),
            'records_stored' => $storedCount,
        ]);

        return [
            'records_fetched' => count($punchData),
            'records_transformed' => count($transformedRecords),
            'records_stored' => $storedCount,
            'data' => $transformedRecords,
        ];
    }

    /**
     * Check if string is JSON
     */
    protected function isJson($string): bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Check if string is CSV
     */
    protected function isCsv($string): bool
    {
        return strpos($string, ',') !== false && strpos($string, "\n") !== false;
    }

    /**
     * Parse CSV response
     */
    protected function parseCsvResponse(string $csvData): array
    {
        $lines = explode("\n", trim($csvData));
        $records = [];
        $headers = null;

        foreach ($lines as $index => $line) {
            $line = trim($line);
            if (empty($line)) continue;

            $fields = str_getcsv($line);

            if ($index === 0 && $this->isHeaderRow($fields)) {
                $headers = $fields;
                continue;
            }

            if ($headers) {
                $record = array_combine($headers, $fields);
            } else {
                // No headers, use positional mapping
                $record = [
                    'Empcode' => $fields[0] ?? null,
                    'Date' => $fields[1] ?? null,
                    'Time' => $fields[2] ?? null,
                    'InOut' => $fields[3] ?? null,
                ];
            }

            if (!empty($record['Empcode'])) {
                $records[] = $record;
            }
        }

        return $records;
    }

    /**
     * Check if row is header row
     */
    protected function isHeaderRow(array $fields): bool
    {
        $headerKeywords = ['empcode', 'employee', 'date', 'time', 'in', 'out', 'punch'];
        $firstField = strtolower($fields[0] ?? '');
        return in_array($firstField, $headerKeywords);
    }

    /**
     * Parse text/line-delimited response
     */
    protected function parseTextResponse(string $textData): array
    {
        $lines = explode("\n", trim($textData));
        $records = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Try to extract data from various formats
            // Common formats: "Empcode,Date,Time,InOut" or "0001|01/01/2018|09:00|IN"
            if (strpos($line, '|') !== false) {
                $fields = explode('|', $line);
            } elseif (strpos($line, ',') !== false) {
                $fields = str_getcsv($line);
            } else {
                // Try space-separated
                $fields = preg_split('/\s+/', $line);
            }

            if (count($fields) >= 3) {
                $records[] = [
                    'Empcode' => $fields[0] ?? null,
                    'Date' => $fields[1] ?? null,
                    'Time' => $fields[2] ?? null,
                    'InOut' => $fields[3] ?? 'IN',
                ];
            }
        }

        return $records;
    }

    /**
     * Transform eTimeOffice records to standard format
     * Handles PunchData array from DownloadPunchData API
     */
    protected function transformRecords(array $records): array
    {
        $transformed = [];

        foreach ($records as $record) {
            try {
                // Extract employee code
                $empCode = $record['Empcode'] ?? $record['empcode'] ?? $record['EmployeeCode'] ?? $record['employee_code'] ?? null;
                if (empty($empCode) || $empCode === 'ALL') {
                    continue;
                }

                // DownloadPunchData API provides PunchDate in format: "dd/MM/yyyy HH:mm:ss"
                $punchDateStr = $record['PunchDate'] ?? null;

                if (empty($punchDateStr)) {
                    Log::warning('Missing PunchDate in eTimeOffice record', [
                        'record' => $record,
                    ]);
                    continue;
                }

                // Parse PunchDate: "15/01/2026 14:59:00" or "15/01/2026 14:59"
                try {
                    // Try format: dd/MM/yyyy HH:mm:ss
                    $punchDateTime = Carbon::createFromFormat('d/m/Y H:i:s', $punchDateStr);
                } catch (\Exception $e) {
                    try {
                        // Try format: dd/MM/yyyy HH:mm
                        $punchDateTime = Carbon::createFromFormat('d/m/Y H:i', $punchDateStr);
                    } catch (\Exception $e2) {
                        // Fallback to Carbon::parse
                        $punchDateTime = Carbon::parse($punchDateStr);
                    }
                }

                // DownloadPunchData doesn't provide IN/OUT status, default to 'in'
                // The system can infer OUT based on multiple punches per day if needed
                $attendanceType = 'in';

                $transformed[] = [
                    'employee_code' => $empCode,
                    'timestamp' => $punchDateTime->toDateTimeString(),
                    'attendance_date' => $punchDateTime->format('Y-m-d'),
                    'punch_in_time' => $punchDateTime->format('H:i:s'),
                    'attendace_type' => $attendanceType,
                    'raw_data' => $record, // Keep raw data for debugging
                ];
            } catch (\Exception $e) {
                Log::warning('Failed to transform eTimeOffice record', [
                    'record' => $record,
                    'error' => $e->getMessage(),
                ]);
                continue;
            }
        }

        return $transformed;
    }

    /**
     * Store attendance records in the database
     * Matches employees by employee_code and company_id
     * Determines IN/OUT based on date-wise logic (first punch = IN, second = OUT, etc.)
     * Generates txn_id using Helper::make_slug with PunchDate
     */
    protected function storeAttendanceRecords(array $records): int
    {
        if (empty($records)) {
            return 0;
        }

        if (!$this->machine || !$this->machine->company_id) {
            Log::error('Cannot store attendance: Machine or company_id not set');
            return 0;
        }

        $companyId = $this->machine->company_id;
        $storedCount = 0;
        $skippedCount = 0;
        $errorCount = 0;

        // Get all unique employee codes from records
        $employeeCodes = array_unique(array_column($records, 'employee_code'));

        // Fetch all employees for this company matching the employee codes
        $employees = Employee::where('company_id', $companyId)
            ->whereIn('biometric_user_id', $employeeCodes)
            ->get()
            ->keyBy('biometric_user_id');

        // Group records by employee and date, then sort by time
        $groupedRecords = [];
        foreach ($records as $record) {
            $empCode = $record['employee_code'];
            $attendanceDate = $record['attendance_date'];
            $key = "{$empCode}_{$attendanceDate}";
            
            if (!isset($groupedRecords[$key])) {
                $groupedRecords[$key] = [];
            }
            $groupedRecords[$key][] = $record;
        }

        // Sort each group by punch time
        foreach ($groupedRecords as $key => $group) {
            usort($groupedRecords[$key], function ($a, $b) {
                $timeA = strtotime($a['attendance_date'] . ' ' . $a['punch_in_time']);
                $timeB = strtotime($b['attendance_date'] . ' ' . $b['punch_in_time']);
                return $timeA <=> $timeB;
            });
        }

        // Process each record with date-wise IN/OUT logic
        foreach ($groupedRecords as $key => $group) {
            $empCode = $group[0]['employee_code'];
            
            // Find employee by employee_code
            $employee = $employees->get($empCode);

            if (!$employee) {
                Log::warning('Employee not found for eTimeOffice attendance', [
                    'employee_code' => $empCode,
                    'company_id' => $companyId,
                ]);
                $skippedCount += count($group);
                continue;
            }

            // Get employee's shift if available
            $shiftId = null;
            if ($employee->employmentDetail && $employee->employmentDetail->shift) {
                $shiftId = $employee->employmentDetail->shift;
            }

            // Process each punch in the group, determining IN/OUT based on last stored record
            foreach ($group as $index => $record) {
                try {
                    $attendanceDate = $record['attendance_date'];
                    $punchTime = $record['punch_in_time'];
                    $punchDateTime = $record['timestamp'];
                    
                    // Get the last attendance record for this employee and date from database
                    // Order by punch_in_time desc to get the most recent record
                    $lastAttendance = Attendance::where('company_id', $companyId)
                        ->where('employee_id', $employee->id)
                        ->where('attendance_date', $attendanceDate)
                        ->orderBy('punch_in_time', 'desc')
                        ->orderBy('created_at', 'desc')
                        ->first();
                    
                    // Determine attendance type based on last stored record
                    if ($lastAttendance) {
                        // If last record is "in", new record should be "out"
                        // If last record is "out", new record should be "in"
                        $lastType = strtolower($lastAttendance->attendace_type ?? '');
                        $attendanceType = ($lastType === 'in') ? 'out' : 'in';
                    } else {
                        // No previous record exists, first one should be "in"
                        $attendanceType = 'in';
                    }

                    // Generate txn_id using Helper::make_slug with PunchDate
                    // Use the original PunchDate from raw_data if available
                    $punchDateForSlug = $record['raw_data']['PunchDate'] ?? $punchDateTime;
                    $txnId = Helper::make_slug($punchDateForSlug . '-' . $empCode . '-' . $punchTime);

                    // Check for duplicate attendance record by txn_id or by employee+date+time+type
                    $duplicate = Attendance::where('company_id', $companyId)
                        ->where(function ($query) use ($txnId, $employee, $attendanceDate, $punchTime, $attendanceType) {
                            $query->where('txn_id', $txnId)
                                ->orWhere(function ($q) use ($employee, $attendanceDate, $punchTime, $attendanceType) {
                                    $q->where('employee_id', $employee->id)
                                        ->where('attendance_date', $attendanceDate)
                                        ->where('punch_in_time', $punchTime)
                                        ->where('attendace_type', $attendanceType);
                                });
                        })
                        ->first();

                    if ($duplicate) {
                        $skippedCount++;
                        continue;
                    }

                    // Create attendance record
                    Attendance::create([
                        'company_id' => $companyId,
                        'employee_id' => $employee->id,
                        'shift_id' => $shiftId,
                        'attendance_date' => $attendanceDate,
                        'create_date' => now()->toDateString(),
                        'punch_in_time' => $punchTime,
                        'attendace_type' => $attendanceType,
                        'remark' => 'Synced from eTimeOffice',
                        'status' => 'active',
                        'txn_id' => $txnId,
                        'records_source' => 'etimeoffice',
                        'requested_data' => json_encode($record['raw_data'] ?? []),
                        'device_serial' => $this->machine->serial_number,
                        'device_ip' => $this->machine->ip_address,
                        'created_by' => $this->machine->created_by,
                    ]);

                    $storedCount++;
                } catch (\Exception $e) {
                    Log::error('Failed to store eTimeOffice attendance record', [
                        'record' => $record,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    $errorCount++;
                }
            }
        }

        Log::info('eTimeOffice attendance storage completed', [
            'machine_id' => $this->machine->id,
            'company_id' => $companyId,
            'stored' => $storedCount,
            'skipped' => $skippedCount,
            'errors' => $errorCount,
        ]);

        return $storedCount;
    }

    /**
     * Sync employees from eTimeOffice API (optional)
     * 
     * TODO: Implement if eTimeOffice provides employee list API
     */
    public function syncEmployees(): array
    {
        // TODO: Implement employee sync if eTimeOffice provides this endpoint
        throw new Exception('Employee sync not implemented yet. eTimeOffice API does not provide employee list endpoint.');
    }

    /**
     * Push salary data to eTimeOffice API (not supported)
     */
    public function pushSalaryData(array $salaryData): array
    {
        return [
            'success' => true,
            'message' => 'Salary push not supported for eTimeOffice',
        ];
    }
}
