<?php

namespace App\Imports;

use App\Models\Attendance;
use App\Models\AttendanceImportFile;
use App\Models\Employee;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class AttendanceImport implements ToCollection, WithHeadingRow
{
    protected $companyId;
    protected $userId;
    protected $attendanceImportFileId;
    protected $authLoginUserDetail;

    protected $totalRows = 0;
    protected $successCount = 0;
    protected $failedCount = 0;
    protected $duplicateCount = 0;
    protected $errors = [];

    public function __construct($companyId, $userId, $attendanceImportFileId, $authLoginUserDetail)
    {
        $this->companyId = $companyId;
        $this->userId = $userId;
        $this->attendanceImportFileId = $attendanceImportFileId;
        $this->authLoginUserDetail = $authLoginUserDetail;
    }

    public function collection(Collection $rows)
    {
        Log::info('Attendance Import Started. Total Rows: ' . $rows->count());

        try {
            DB::beginTransaction();
            foreach ($rows as $index => $row) {
                
                $rowNumber = $index + 2;
                if ($row->filter()->isEmpty()) continue;

                $this->totalRows++;
                $validationErrors = [];
                
                // Extract employee identifier (support multiple column names)
                $biometricUserId = $this->getValue($row, ['biometric_user_id', 'biometric_userid', 'card_no', 'cardno', 'Card No', 'Card Number', 'CARD NO', 'CardNo', 'Employee Code:-', 'Employee Code']);
                $employeeCode = $this->getValue($row, [
                    'employee_code', 'emp_code', 'employee code', 's.no employee code', 
                    'Employee Code', 'EMP CODE', 'Emp Code', 'empcode', 'EMPLOYEE CODE',
                    'Emp. Code', 'Emp Code', 'Code', 'Employee ID', 'Emp ID', 'EmployeeID'
                ]);
                $employeeName = $this->getValue($row, [
                    'employee_name', 'employee name', 'name', 'full_name', 
                    'Employee Name', 'EMP NAME', 'Emp Name', 'empname', 'EMPLOYEE NAME',
                    'Full Name', 'FullName', 'Name', 'Employee', 'Emp Name', 'Emp. Name'
                ]);
                
                // Extract attendance date (support multiple formats)
                $attendanceDate = $this->parseDate($this->getValue($row, ['attendance_date', 'date', 'attendance_dt', 'attendance date', 'Attendance Date-', 'Attendance Date']));
                
                // Extract times
                $punchInTime = $this->parseTime($this->getValue($row, ['punch_in_time', 'punch_time', 'time', 'punch_in', 'a. intime', 'intime']));
                $punchOutTime = $this->parseTime($this->getValue($row, ['punch_out_time', 'punch_out', 'a.outtime', 'outtime', 'a. outtime']));
                
                // Extract attendance type
                $attendanceType = strtolower(trim($this->getValue($row, ['attendace_type', 'attendance_type', 'type', 'punch_type']) ?? ''));
                
                // Extract optional fields
                $shiftId = $this->getValue($row, ['shift_id', 'shift']);
                $remark = $this->getValue($row, ['remark', 'remarks', 'notes']);
                $punchId = $this->getValue($row, ['punch_id', 'punchid', 'transaction_id']);
                $txnId = $this->getValue($row, ['txn_id', 'txnid', 'transaction_id']);
                $deviceSerial = $this->getValue($row, ['device_serial', 'device_serial_number']);
                $deviceIp = $this->getValue($row, ['device_ip', 'device_ip_address']);
                $status = strtoupper(trim($this->getValue($row, ['status']) ?? ''));
                
                if ($index == 1) {
                    dd("LN-85", $row, $biometricUserId, $index, $rowNumber, $employeeCode, $employeeName);
                }
                
                // Validation - At least one employee identifier must be provided
                if (!$biometricUserId && !$employeeCode && !$employeeName) {
                    $this->failedCount++;
                    $this->errors[$rowNumber] = 'Employee identifier (biometric_user_id, employee_code, or employee_name) is missing';
                    dd("L-91", $row, $validationErrors, $this->errors);
                    continue;
                }
                dd("L-89", $row, $validationErrors, $biometricUserId, $employeeCode, $employeeName, $attendanceDate, $punchInTime, $punchOutTime, $attendanceType, $shiftId, $remark, $punchId, $txnId, $deviceSerial, $deviceIp, $status);
                if (!$attendanceDate) {
                    $validationErrors[] = 'Invalid or missing Attendance Date';
                }

                // If status is 'A' (Absent), skip this row
                if ($status === 'A' || $status === 'ABSENT') {
                    $this->duplicateCount++;
                    continue;
                }

                // If no punch times, skip
                if (!$punchInTime && !$punchOutTime) {
                    $validationErrors[] = 'Both punch_in_time and punch_out_time are missing';
                }

                if (!empty($validationErrors)) {
                    $this->failedCount++;
                    $this->errors[$rowNumber] = implode(', ', $validationErrors);
                    continue;
                }

                // Find employee (priority: biometric_user_id > employee_code > employee_name)
                $employee = $this->findEmployee($biometricUserId, $employeeCode, $employeeName, $validationErrors);
                
                if (!$employee) {
                    $this->failedCount++;
                    $this->errors[$rowNumber] = implode(', ', $validationErrors);
                    continue;
                }

                // Find shift
                $shift = $this->findShift($shiftId, $validationErrors);

                // Process attendance records
                try {
                    // If both punch_in and punch_out exist, create two records
                    if ($punchInTime && $punchOutTime) {
                        // Create 'in' record
                        $this->createAttendanceRecord($employee->id, $shift?->id, $attendanceDate, $punchInTime, 'in', $remark, $punchId, $txnId, $deviceSerial, $deviceIp, $rowNumber);
                        
                        // Create 'out' record
                        $this->createAttendanceRecord($employee->id, $shift?->id, $attendanceDate, $punchOutTime, 'out', $remark, $punchId, $txnId, $deviceSerial, $deviceIp, $rowNumber);
                    } 
                    // If only punch_in exists
                    elseif ($punchInTime) {
                        $finalType = $attendanceType ?: 'in';
                        $this->createAttendanceRecord($employee->id, $shift?->id, $attendanceDate, $punchInTime, $finalType, $remark, $punchId, $txnId, $deviceSerial, $deviceIp, $rowNumber);
                    }
                    // If only punch_out exists
                    elseif ($punchOutTime) {
                        $finalType = $attendanceType ?: 'out';
                        $this->createAttendanceRecord($employee->id, $shift?->id, $attendanceDate, $punchOutTime, $finalType, $remark, $punchId, $txnId, $deviceSerial, $deviceIp, $rowNumber);
                    }
                    $this->successCount++;
                } catch (\Exception $e) {
                    $this->failedCount++;
                    $this->errors[$rowNumber] = $e->getMessage();
                    Log::error("Attendance Import Error at Row {$rowNumber}: " . $e->getMessage());
                }
            }
            dd("LN-151", $this->totalRows, $this->successCount, $this->failedCount, $this->duplicateCount, $this->errors);
            // Commit transaction after processing all rows
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->failedCount++;
            $errorMessage = "Fatal error during import: " . $e->getMessage();
            $this->errors['fatal'] = $errorMessage;
            Log::error("Attendance Import Fatal Error: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        } finally {
            // Always update import file record, even if there was an error
            try {
                AttendanceImportFile::where('id', $this->attendanceImportFileId)->update([
                    'total_rows' => $this->totalRows,
                    'total_success' => $this->successCount,
                    'total_failed' => $this->failedCount,
                    'total_duplicates' => $this->duplicateCount,
                    'errors' => json_encode($this->errors),
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);

                Log::info("Attendance Import Completed: Total {$this->totalRows}, Success {$this->successCount}, Failed {$this->failedCount}, Duplicates {$this->duplicateCount}");
            } catch (\Exception $e) {
                Log::error("Failed to update import file record: " . $e->getMessage());
            }
        }
    }

    /**
     * Get value from row with multiple possible column names
     */
    private function getValue($row, array $possibleKeys)
    {
        foreach ($possibleKeys as $key) {
            $normalizedKey = strtolower(str_replace([' ', '_', '-'], '', $key));
            foreach ($row->keys() as $rowKey) {
                $normalizedRowKey = strtolower(str_replace([' ', '_', '-'], '', $rowKey));
                if ($normalizedKey === $normalizedRowKey) {
                    $value = $row[$rowKey] ?? null;
                    if ($value !== null && $value !== '') {
                        return trim($value);
                    }
                }
            }
        }
        return null;
    }

    /**
     * Find employee by priority: biometric_user_id > employee_code > employee_name
     */
    private function findEmployee($biometricUserId, $employeeCode, $employeeName, &$validationErrors)
    {
        // Priority 1: biometric_user_id
        if ($biometricUserId) {
            $employee = Employee::where('company_id', $this->companyId)
                ->where('biometric_user_id', $biometricUserId)
                ->first();
            if ($employee) {
                return $employee;
            }
        }

        // Priority 2: employee_code
        if ($employeeCode) {
            $employee = Employee::where('company_id', $this->companyId)
                ->where('employee_code', $employeeCode)
                ->first();
            if ($employee) {
                return $employee;
            }
        }

        // Priority 3: employee_name (search in full_name, first_name, middle_name)
        if ($employeeName) {
            $employee = Employee::where('company_id', $this->companyId)
                ->where(function($q) use ($employeeName) {
                    $q->where('full_name', 'LIKE', "%{$employeeName}%")
                      ->orWhere('first_name', 'LIKE', "%{$employeeName}%")
                      ->orWhere(DB::raw("CONCAT(first_name, ' ', middle_name)"), 'LIKE', "%{$employeeName}%");
                })
                ->first();
            if ($employee) {
                return $employee;
            }
        }

        $identifier = $biometricUserId ?: ($employeeCode ?: $employeeName);
        $validationErrors[] = "Employee not found: {$identifier}";
        return null;
    }

    /**
     * Find shift by ID or name
     */
    private function findShift($shiftId, &$validationErrors)
    {
        if (!$shiftId) {
            return null;
        }

        // If numeric, try as ID first
        if (is_numeric($shiftId)) {
            $shift = Shift::where('company_id', $this->companyId)
                ->where('id', $shiftId)
                ->first();
            if ($shift) {
                return $shift;
            }
        }

        // Try as name
        $shift = Shift::where('company_id', $this->companyId)
            ->whereRaw("LOWER(name) = ?", [strtolower(trim($shiftId))])
            ->first();

        if (!$shift) {
            $validationErrors[] = "Shift '{$shiftId}' not found";
        }

        return $shift;
    }

    /**
     * Create attendance record with duplicate check
     */
    private function createAttendanceRecord($employeeId, $shiftId, $attendanceDate, $punchTime, $attendanceType, $remark, $punchId, $txnId, $deviceSerial, $deviceIp, $rowNumber)
    {
        // Check for duplicate
        $duplicate = Attendance::where('company_id', $this->companyId)
            ->where('employee_id', $employeeId)
            ->where('attendance_date', $attendanceDate)
            ->where('punch_in_time', $punchTime)
            ->where('attendace_type', $attendanceType)
            ->first();

        if ($duplicate) {
            $this->duplicateCount++;
            Log::info("Duplicate attendance skipped at row {$rowNumber}: Employee {$employeeId}, Date {$attendanceDate}, Time {$punchTime}, Type {$attendanceType}");
            return;
        }

        // Create attendance record
        Attendance::create([
            'company_id' => $this->companyId,
            'employee_id' => $employeeId,
            'shift_id' => $shiftId,
            'attendance_date' => $attendanceDate,
            'create_date' => now()->toDateString(),
            'punch_in_time' => $punchTime,
            'attendace_type' => $attendanceType,
            'remark' => $remark,
            'status' => 'active',
            'punch_id' => $punchId,
            'txn_id' => $txnId,
            'records_source' => 'excel_import',
            'requested_data' => json_encode([
                'row_number' => $rowNumber,
                'import_file_id' => $this->attendanceImportFileId,
            ]),
            'device_serial' => $deviceSerial,
            'device_ip' => $deviceIp,
            'created_by' => $this->userId,
        ]);
    }

    /**
     * Parse date from various formats
     */
    private function parseDate($value)
    {
        if (!$value) return null;
        
        try {
            // If it's Excel date value (numeric)
            if (is_numeric($value)) {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value))->format('Y-m-d');
            }
            
            // Try different date formats
            $formats = ['Y-m-d', 'd/m/Y', 'd-m-Y', 'd-M-Y', 'd-M-y', 'Y/m/d'];
            foreach ($formats as $format) {
                try {
                    return Carbon::createFromFormat($format, trim($value))->format('Y-m-d');
                } catch (\Exception $e) {
                    continue;
                }
            }
            
            // Try Carbon parse as fallback
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            Log::warning("Date parsing failed for value: {$value}");
            return null;
        }
    }

    /**
     * Parse time from various formats
     */
    private function parseTime($value)
    {
        if (!$value || $value === '00:00' || $value === '00:00:00') {
            return null;
        }
        
        try {
            // If it's Excel time value (numeric between 0 and 1)
            if (is_numeric($value) && $value >= 0 && $value < 1) {
                $time = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);
                return $time->format('H:i:s');
            }
            
            // Try different time formats
            $formats = ['H:i:s', 'H:i', 'h:i:s A', 'h:i A'];
            foreach ($formats as $format) {
                try {
                    $time = Carbon::createFromFormat($format, trim($value));
                    return $time->format('H:i:s');
                } catch (\Exception $e) {
                    continue;
                }
            }
            
            // Try Carbon parse as fallback
            return Carbon::parse($value)->format('H:i:s');
        } catch (\Exception $e) {
            Log::warning("Time parsing failed for value: {$value}");
            return null;
        }
    }
}

