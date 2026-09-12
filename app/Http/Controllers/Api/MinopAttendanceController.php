<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\BiometricMachine;
use App\Models\Company;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Models\Shift;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Cache;

class MinopAttendanceController extends Controller
{
    /**
     * "Say Hello" API.
     * The device calls this to register itself or report status.
     * Expected payload: { "app_key": "...", "dvcSrNo": "...", "dvcTime": "..." }
     */
    public function say_hello(Request $request, $app_key = null)
    {

        try {
            $validator = Validator::make($request->all(), [
                'app_key' => [
                    'required',
                    Rule::exists((new Company())->getTable(), 'app_key')
                ]
            ]);

            if ($validator->fails()) {
                return $this->sendError($validator->messages()->first(), $validator->messages(), [], 401);
            }

            // Log request for debugging
            Log::info('Minop SayHello: ' . json_encode($request->all()));

            // Fetch company from app_key (validated above)
            $appKey = $request->input('app_key');
            $company = Company::where('app_key', $appKey)->first();

            if (!$company) {
                return $this->sendError('Company not found', [], [], 404);
            }

            if ($company?->current_latest_plan?->subscription_status == 'expired') {
                return $this->sendError('Company subscription is expired.', [], [], 403);
            }

            if ($company->status !== 'active') {
                return $this->sendError('Company is ' . $company?->status, [], [], 403);
            }

            $serialNumber = $request->input('dvcSrNo');
            $deviceTime = $request->input('dvcTime');

            if (!$serialNumber) {
                return $this->sendError('Missing dvcSrNo', [], [], 400);
            }

            // Find or create device by serial number
            // Note: Company association might be tricky here if we don't have company info in payload.
            // Assuming we update existing if found, or create new (company_id might need to be resolved or nullable)

            $machine = BiometricMachine::where('serial_number', $serialNumber)->where('company_id', $company->id)->where('status', 'active')->first();

            if ($machine) {
                // Update last active time or description if needed? 
                // For now just ack.
                $machine->touch();
            } else {

                if ($company) {
                    BiometricMachine::create([
                        'company_id' => $company->id,
                        'serial_number' => $serialNumber,
                        'machine_name' => $serialNumber, // Default name
                        'ip_address' => '0.0.0.0', // Required placeholder
                        'port' => '0', // Required placeholder
                        'status' => 'inactive',
                        'description' => 'Auto-registered via SayHello',
                    ]);
                    Log::info("Minop SayHello: Created new inactive device {$serialNumber} for Company {$company->id}");
                } else {
                    Log::warning("Minop SayHello: Device {$serialNumber} not found and Company not found for app_key {$appKey}.");
                }
            }

            return response()->json(['status' => 1]);
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    /**
     * Receive attendance data from Minop Cloud biometric devices.
     * Accepts flexible payloads (array under `records`, `data`, `attendance`, `trans` or single record).
     * Tries to match employee by `employee_code` or `biometric_user_id` (if that column exists).
     */
    public function receive_attendance(Request $request, $company_app_key = null)
    {
        // Log request for debugging

        Log::info('Minop receive_attendance: ' . Carbon::now()->format('Y-m-d H:i:s'));
        Log::info($request->all());

        /*
        return response()->json([
            'status' => false,
            'message' => 'API under maintenance',
        ], 200);
        */

        $payload = $request->all();

        // Try to resolve company from app_key (preferred), header, or route param (in that order)
        $company = null;
        $appKeyFromRequest = $request->input('app_key') ?? $request->get('app_key') ?? null;

        // If app_key provided in request body/params, try to resolve company by that
        if (!empty($appKeyFromRequest)) {
            $company = Company::where('app_key', $appKeyFromRequest)->first();
        }
        // If still not resolved, fall back to route param
        if (!$company && !empty($company_app_key)) {
            if (is_numeric($company_app_key)) {
                $company = Company::find($company_app_key);
            } else {
                $company = Company::where('app_key', $company_app_key)->orWhere('id', $company_app_key)->first();
            }
        }

        // If not found, return unauthorized
        if (!$company) {
            return $this->sendError('Unauthorized', [], [], 403);
        }

        if ($company?->current_latest_plan?->subscription_status == 'expired') {
            return $this->sendError('Company subscription is expired.', [], [], 403);
        }

        if ($company->status !== 'active') {
            return $this->sendError('Company is ' . $company?->status, [], [], 403);
        }

        // Normalize records
        if (!empty($payload['records']) && is_array($payload['records'])) {
            $records = $payload['records'];
        } elseif (!empty($payload['data']) && is_array($payload['data'])) {
            $records = $payload['data'];
        } elseif (!empty($payload['attendance']) && is_array($payload['attendance'])) {
            $records = $payload['attendance'];
        } elseif (!empty($payload['trans']) && is_array($payload['trans'])) {
            // Minop docs example shows "trans"
            $records = $payload['trans'];
        } else {
            // single record
            $records = [$payload];
        }

        $processed = 0;
        $created = 0;
        $updated = 0;
        $existedTxnIds = [];
        $returnResponse = [];
        $skipped = 0;
        $errors = [];

        foreach ($records as $rec) {
            // Common keys that biometric providers may send
            $identifier = $rec['employee_code'] ?? $rec['emp_code'] ?? $rec['card_no'] ?? $rec['id'] ?? $rec['punchId'] ?? null;
            $timestamp = $rec['timestamp'] ?? $rec['time'] ?? $rec['punch_time'] ?? $rec['datetime'] ?? $rec['txnDateTime'] ?? null;
            $type = strtolower($rec['type'] ?? $rec['status'] ?? $rec['in_out'] ?? $rec['mode'] ?? 'in');

            if (empty($identifier) || empty($timestamp)) {
                $skipped++;
                continue;
            }

            // Find employee: prefer biometric id column if exists, else employee_code
            $employeeQuery = Employee::query();
            $employee = null;
            try {
                if (Schema::hasColumn((new Employee())->getTable(), 'biometric_user_id')) {
                    $employee = $employeeQuery->where(function ($q) use ($identifier, $company) {
                        $q->where('biometric_user_id', $identifier)->orWhere('employee_code', $identifier);
                        if ($company) {
                            $q->where('company_id', $company->id);
                        }
                    })->first();
                } else {
                    $employee = $employeeQuery->where('employee_code', $identifier)->when($company, function ($q) use ($company) {
                        $q->where('company_id', $company->id);
                    })->first();
                }
            } catch (\Exception $e) {
                Log::error('MinopAttendanceController employee lookup error: ' . $e->getMessage());
            }

            if (!$employee) {
                $errors[] = "Employee not found: {$identifier}";
                $skipped++;
                continue;
            }

            // Parse timestamp flexibly
            try {
                $dt = Carbon::parse($timestamp);
            } catch (\Exception $e) {
                $errors[] = "Invalid timestamp: {$timestamp}";
                $skipped++;
                continue;
            }

            $attendanceDate = $dt->format('Y-m-d');
            $punchTime = $dt->format('H:i:s');

            // Map to Attendance fields
            // Minop standard fields: txnId, dvcId, dvcIP, punchId, txnDatetime, mode
            $punchId = $rec['punchId'] ?? $rec['txnId'] ?? null;
            $deviceSerial = $rec['dvcSrNo'] ?? $rec['dvcId'] ?? null; // Sometimes dvcId is serial
            $deviceIp = $rec['dvcIP'] ?? null;

            // Resolve Shift ID
            $shiftId = $employee->employmentDetail?->shift;
            if (empty($shiftId)) {
                // Fallback to first shift of company or '0'
                $defaultShift = Shift::where('company_id', $employee->company_id)->first();
                $shiftId = $defaultShift ? $defaultShift->id : '0';
            }

            // Determine correct attendance type: only allow 'in' or 'out'
            // If $type is not 'in' or 'out', infer based on last punch for this employee on the date
            if (!in_array(strtolower($type), ['in', 'out'])) {
                // Get the last attendance punch for the employee on this date, order by punch_in_time or created_at descending
                $lastPunch = \App\Models\Attendance::where('employee_id', $employee->id)
                    ->where('attendance_date', $attendanceDate)
                    ->orderBy('punch_in_time', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($lastPunch && strtolower($lastPunch->attendace_type) == 'in') {
                    $type = 'out';
                } else {
                    $type = 'in';
                }
            } else {
                // Even if provided, normalize to all lower-case for consistency
                $type = strtolower($type);
            }

            // Add txnId to $data (nullable, get from request if present)
            $txnId = $rec['txnId'] ?? $rec['txnId'] ?? null;

            $data = [
                'company_id' => $employee->company_id,
                'employee_id' => $employee->id,
                'shift_id' => $shiftId,
                'attendance_date' => $attendanceDate,
                'create_date' => $dt->toDateTimeString(),
                'punch_in_time' => $punchTime,
                'attendace_type' => $type,
                'remark' => $rec['remark'] ?? null,
                'status' => $rec['status'] ?? 'active',
                'punch_id' => $punchId,
                'txn_id' => $txnId,
                'records_source' => $rec['records_source'] ?? 'api',
                'requested_data' => json_encode($rec),
                'device_serial' => $deviceSerial,
                'device_ip' => $deviceIp,
            ];

            // Upsert / Deduplication logic

            // 1. Try to find by punchId + company_id or txn_id + company_id if either exists
            $existing = null;
            // If txnId present and not found above, search by txn_id as well
            if (!empty($txnId)) {
                $existing = Attendance::where('txn_id', $txnId)
                    ->where('company_id', $employee?->company_id ?? null)
                    ->first();
            }

            // If not found by punchId or txnId, check dup by employee + date + time + type
            if (!$existing) {
                $existing = Attendance::where('employee_id', $employee?->id ?? null)
                    ->where('attendance_date', $attendanceDate)
                    ->where('punch_in_time', $punchTime)
                    ->where('attendace_type', $type)
                    ->first();
            }

            // dd("L-251", $identifier, $timestamp, $type, $rec, $data, $existing);

            // 3. Fallback: maybe just date/type check if we don't want multiple INs per day (Optional, but user asked for "duplicate data" handling)
            //  "i can store store duplicate data in the system also handle it" -> implies we should allowing storing it BUT handle missing punchId carefully? 
            // Actually, usually biometric systems shouldn't duplicate punches. 
            // If we found a match above (same time/date/emp), we treat it as existing.

            try {
                if ($existing) {
                    // $existing->update($data);
                    // $updated++;
                    $existedTxnIds[] = $existing?->id ?? null;

                    // Check if request txnDateTime == attendance_date + punch_in_time
                    $requestedTxnDateTime = null;
                    if (!empty($rec['txnDateTime'])) {
                        $requestedTxnDateTime = $rec['txnDateTime'];
                    } elseif (!empty($rec['datetime'])) {
                        $requestedTxnDateTime = $rec['datetime'];
                    } elseif (!empty($rec['timestamp'])) {
                        $requestedTxnDateTime = $rec['timestamp'];
                    } elseif (!empty($rec['time'])) {
                        $requestedTxnDateTime = $rec['time'];
                    }

                    $attendance_date = $existing->attendance_date ?? null;
                    $punch_in_time = $existing->punch_in_time ?? null;

                    $combinedAttendanceDateTime = null;
                    if ($attendance_date && $punch_in_time) {
                        $combinedAttendanceDateTime = trim($attendance_date . ' ' . $punch_in_time);
                    }

                    if ($requestedTxnDateTime && $combinedAttendanceDateTime) {
                        // Use Carbon to fairly compare formatted datetimes
                        try {
                            $r1 = \Carbon\Carbon::parse($requestedTxnDateTime)->format('Y-m-d H:i:s');
                            $r2 = \Carbon\Carbon::parse($combinedAttendanceDateTime)->format('Y-m-d H:i:s');
                            $statusFlag = ($r1 === $r2) ? 1 : 0;
                        } catch (\Exception $e) {
                            $statusFlag = 0;
                        }
                    } else {
                        $statusFlag = 0;
                    }

                    $returnResponse[] = [
                        'txnId' => $txnId,
                        'status' => $statusFlag,
                    ];
                } else {
                    Attendance::create($data);
                    $created++;
                    $returnResponse[] = [
                        'txnId' => $txnId,
                        'status' => 1,
                    ];
                }
                $processed++;
            } catch (\Exception $e) {
                $errors[] = "DB error for {$identifier} at {$timestamp}: " . $e->getMessage();
                Log::error('MinopAttendanceController DB error: ' . $e->getMessage());
            }
        }

        return response()->json([
            // 'status' => true,
            // 'processed' => $processed,
            // 'created' => $created,
            // // 'updated' => $updated,
            // 'existedTxnIds' => $existedTxnIds,
            // 'skipped' => $skipped,
            // 'errors' => $errors,
            'transStatus' => $returnResponse,
        ], 200);
    }

    /**
     * Receive device list from Minop Cloud and upsert BiometricMachine records.
     * Expects an array of devices under `devices`, `data`, or the root payload.
     */
    public function devices(Request $request, $biomax_company_name = null)
    {
        // Optional API key check
        $apiKey = $request->header('X-API-KEY') ?? $request->header('Api-Key');
        if (config('services.minop.api_key') && $apiKey !== config('services.minop.api_key')) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        $payload = $request->all();

        // resolve company if provided (prefer app_key over route)
        $company = null;
        $appKeyFromRequest = $request->input('app_key') ?? $request->get('app_key') ?? null;
        $appKeyFromHeader = $request->header('X-API-KEY') ?? $request->header('Api-Key') ?? null;

        if (!empty($appKeyFromRequest)) {
            $company = Company::where('app_key', $appKeyFromRequest)->first();
        }
        if (!$company && !empty($appKeyFromHeader)) {
            if (!(config('services.minop.api_key') && $appKeyFromHeader === config('services.minop.api_key'))) {
                $company = Company::where('app_key', $appKeyFromHeader)->first();
            }
        }

        if (!$company && !empty($biomax_company_name)) {
            if (is_numeric($biomax_company_name)) {
                $company = Company::find($biomax_company_name);
            } else {
                $company = Company::where('company_name', $biomax_company_name)->first();
                if (!$company) {
                    $company = Company::where('id', $biomax_company_name)->first();
                }
            }
        }

        if (!empty($payload['devices']) && is_array($payload['devices'])) {
            $devices = $payload['devices'];
        } elseif (!empty($payload['data']) && is_array($payload['data'])) {
            $devices = $payload['data'];
        } else {
            $devices = is_array($payload) ? $payload : [$payload];
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors = [];

        foreach ($devices as $d) {
            // try common keys
            $ip = $d['ip'] ?? $d['ip_address'] ?? $d['ipaddr'] ?? null;
            $name = $d['name'] ?? $d['machine_name'] ?? $d['device_name'] ?? null;
            $port = $d['port'] ?? $d['server_port'] ?? null;
            $status = $d['status'] ?? 'active';
            $description = $d['description'] ?? $d['desc'] ?? null;

            if (empty($ip) && empty($name)) {
                $skipped++;
                continue;
            }

            try {
                $query = BiometricMachine::query();
                if ($ip) {
                    $query->where('ip_address', $ip);
                } else {
                    $query->where('machine_name', $name);
                }
                if ($company) {
                    $query->where('company_id', $company->id);
                }
                $machine = $query->first();

                $payloadData = [
                    'company_id' => $company->id ?? ($d['company_id'] ?? null),
                    'machine_name' => $name ?? $ip,
                    'ip_address' => $ip,
                    'port' => $port,
                    'status' => $status,
                    'description' => $description,
                ];

                if ($machine) {
                    $machine->update($payloadData);
                    $updated++;
                } else {
                    BiometricMachine::create($payloadData);
                    $created++;
                }
            } catch (\Exception $e) {
                $errors[] = $e->getMessage();
                Log::error('MinopAttendanceController devices error: ' . $e->getMessage());
            }
        }

        return response()->json([
            'status' => true,
            'created' => $created,
            'updated' => $updated,
            'skipped' => $skipped,
            'errors' => $errors,
        ]);
    }

    /**
     * Receive attendance data from 3rd party systems (like CRM) using biomax_id.
     * Similar to receive_attendance but uses biomax_id (biometric_user_id) for employee lookup.
     * Includes rate limiting per company (configurable per minutes).
     * Stores single entries in the database.
     */
    public function attendance_receive_3rd_party(Request $request)
    {
        // Log request for debugging
        Log::info('3rd Party Attendance receive: ' . Carbon::now()->format('Y-m-d H:i:s'));
        Log::info($request->all());

        $payload = $request->all();

        // Try to resolve company from app_key (preferred), header, or request body
        $company = null;
        $appKeyFromRequest = $request->input('app_key') ?? $request->header('X-API-KEY') ?? $request->header('Api-Key') ?? null;

        // If app_key provided in request body/params, try to resolve company by that
        if (!empty($appKeyFromRequest)) {
            $company = Company::where('app_key', $appKeyFromRequest)->first();
        }

        // If still not resolved, try company_id from request
        if (!$company && $request->has('company_id')) {
            $company = Company::find($request->input('company_id'));
        }

        // If not found, return unauthorized
        if (!$company) {
            return $this->sendError('Unauthorized: Company not found', [], [], 403);
        }

        if ($company?->current_latest_plan?->subscription_status == 'expired') {
            return $this->sendError('Company subscription is expired.', [], [], 403);
        }

        if ($company->status !== 'active') {
            return $this->sendError('Company is ' . $company?->status, [], [], 403);
        }

        // Rate limiting check - per company, per minute
        $rateLimitKey = 'attendance_3rd_party_rate_limit_' . $company->id;
        $rateLimit = $company->company_details?->attendance_request_rate_limit_per_minutes ?? 60; // Default 60 requests per minute

        $currentRequests = Cache::get($rateLimitKey, 0);

        if ($currentRequests >= $rateLimit) {
            Log::warning("Rate limit exceeded for company {$company->id}. Current: {$currentRequests}, Limit: {$rateLimit}");
            return $this->sendError("Rate limit exceeded. Maximum {$rateLimit} requests per minute allowed.", [], [], 429);
        }

        // Increment rate limit counter (expires in 60 seconds)
        Cache::put($rateLimitKey, $currentRequests + 1, 60);

        // Normalize records - handle both single record and array of records
        if (!empty($payload['records']) && is_array($payload['records'])) {
            $records = $payload['records'];
        } elseif (!empty($payload['data']) && is_array($payload['data'])) {
            $records = $payload['data'];
        } elseif (!empty($payload['attendance']) && is_array($payload['attendance'])) {
            $records = $payload['attendance'];
        } elseif (!empty($payload['attendances']) && is_array($payload['attendances'])) {
            $records = $payload['attendances'];
        } elseif (!empty($payload['trans']) && is_array($payload['trans'])) {
            $records = $payload['trans'];
        } else {
            // Single record
            $records = [$payload];
        }

        $processed = 0;
        $created = 0;
        $skipped = 0;
        $errors = [];
        $returnResponse = [];

        // Process each record individually
        foreach ($records as $rec) {
            try {
                // Extract biomax_id (biometric_user_id) - this is the key identifier for 3rd party systems
                $biomaxId = $rec['biomax_id'] ?? $rec['biometric_user_id'] ?? $rec['biometric_id'] ?? $rec['user_id'] ?? null;

                // Fallback to employee_code if biomax_id not provided
                if (empty($biomaxId)) {
                    $biomaxId = $rec['employee_code'] ?? $rec['emp_code'] ?? $rec['card_no'] ?? null;
                }

                $timestamp = $rec['timestamp'] ?? $rec['time'] ?? $rec['punch_time'] ?? $rec['datetime'] ?? $rec['txnDateTime'] ?? $rec['date_time'] ?? null;
                $type = strtolower($rec['type'] ?? $rec['status'] ?? $rec['in_out'] ?? $rec['mode'] ?? 'in');

                if (empty($biomaxId) || empty($timestamp)) {
                    $skipped++;
                    $errors[] = "Missing biomax_id or timestamp in record";
                    continue;
                }

                // Find employee by biometric_user_id (biomax_id) or employee_code
                $employee = null;
                try {
                    if (Schema::hasColumn((new Employee())->getTable(), 'biometric_user_id')) {
                        $employee = Employee::where(function ($q) use ($biomaxId, $company) {
                            $q->where('biometric_user_id', $biomaxId)
                                ->orWhere('employee_code', $biomaxId);
                            if ($company) {
                                $q->where('company_id', $company->id);
                            }
                        })->first();
                    } else {
                        $employee = Employee::where('employee_code', $biomaxId)
                            ->when($company, function ($q) use ($company) {
                                $q->where('company_id', $company->id);
                            })
                            ->first();
                    }
                } catch (\Exception $e) {
                    Log::error('3rd Party Attendance employee lookup error: ' . $e->getMessage());
                }

                if (!$employee) {
                    $errors[] = "Employee not found for biomax_id: {$biomaxId}";
                    $skipped++;
                    continue;
                }

                // Parse timestamp flexibly
                try {
                    $dt = Carbon::parse($timestamp);
                } catch (\Exception $e) {
                    $errors[] = "Invalid timestamp: {$timestamp}";
                    $skipped++;
                    continue;
                }

                $attendanceDate = $dt->format('Y-m-d');
                $punchTime = $dt->format('H:i:s');

                // Extract additional fields
                $punchId = $rec['punchId'] ?? $rec['txnId'] ?? $rec['transaction_id'] ?? null;
                $deviceSerial = $rec['dvcSrNo'] ?? $rec['dvcId'] ?? $rec['device_serial'] ?? $rec['serial_number'] ?? null;
                $deviceIp = $rec['dvcIP'] ?? $rec['device_ip'] ?? $rec['ip_address'] ?? null;
                $txnId = $rec['txnId'] ?? $rec['transaction_id'] ?? $rec['id'] ?? null;

                // Resolve Shift ID
                $shiftId = $employee->employmentDetail?->shift ?? null;
                if (empty($shiftId)) {
                    // Fallback to first shift of company or '0'
                    $defaultShift = Shift::where('company_id', $employee->company_id)->first();
                    $shiftId = $defaultShift ? $defaultShift->id : '0';
                }

                // Determine correct attendance type: only allow 'in' or 'out'
                if (!in_array(strtolower($type), ['in', 'out'])) {
                    // Get the last attendance punch for the employee on this date
                    $lastPunch = Attendance::where('employee_id', $employee->id)
                        ->where('attendance_date', $attendanceDate)
                        ->orderBy('punch_in_time', 'desc')
                        ->orderBy('created_at', 'desc')
                        ->first();

                    if ($lastPunch && strtolower($lastPunch->attendace_type) == 'in') {
                        $type = 'out';
                    } else {
                        $type = 'in';
                    }
                } else {
                    $type = strtolower($type);
                }

                // Prepare attendance data
                $data = [
                    'company_id' => $employee->company_id,
                    'employee_id' => $employee->id,
                    'shift_id' => $shiftId,
                    'attendance_date' => $attendanceDate,
                    'create_date' => $dt->toDateTimeString(),
                    'punch_in_time' => $punchTime,
                    'attendace_type' => $type,
                    'remark' => $rec['remark'] ?? $rec['notes'] ?? null,
                    'status' => $rec['status'] ?? 'active',
                    'punch_id' => $punchId,
                    'txn_id' => $txnId,
                    'records_source' => '3rd_party_api',
                    'requested_data' => json_encode($rec),
                    'device_serial' => $deviceSerial,
                    'device_ip' => $deviceIp,
                ];

                // Check for existing record by txn_id or duplicate check
                $existing = null;
                if (!empty($txnId)) {
                    $existing = Attendance::where('txn_id', $txnId)
                        ->where('company_id', $employee->company_id)
                        ->first();
                }

                // If not found by txnId, check duplicate by employee + date + time + type
                if (!$existing) {
                    $existing = Attendance::where('employee_id', $employee->id)
                        ->where('attendance_date', $attendanceDate)
                        ->where('punch_in_time', $punchTime)
                        ->where('attendace_type', $type)
                        ->first();
                }

                if ($existing) {
                    // Record already exists - return status 1 (success) but don't create duplicate
                    $returnResponse[] = [
                        'txnId' => $txnId ?? $punchId,
                        'biomax_id' => $biomaxId,
                        'status' => 1,
                        'message' => 'Record already exists'
                    ];
                    $processed++;
                } else {
                    // Create new attendance record
                    Attendance::create($data);
                    $created++;
                    $returnResponse[] = [
                        'txnId' => $txnId ?? $punchId,
                        'biomax_id' => $biomaxId,
                        'status' => 1,
                        'message' => 'Record created successfully'
                    ];
                    $processed++;
                }
            } catch (\Exception $e) {
                $errors[] = "Error processing record: " . $e->getMessage();
                Log::error('3rd Party Attendance DB error: ' . $e->getMessage());
                $skipped++;
            }
        }

        return $this->sendResponse([
            'processed' => $processed,
            'created' => $created,
            'skipped' => $skipped,
            'errors' => $errors,
            'transStatus' => $returnResponse,
        ], 'Attendance received successfully');
    }
}
