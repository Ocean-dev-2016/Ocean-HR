<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class EmployeeController extends Controller
{
    /**
     * Specialized Punch In API for website/app.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function punch_in(Request $request)
    {
        $request->merge(['attendace_type' => 'in']);
        return $this->punch_in_out($request);
    }

    /**
     * Specialized Punch Out API for website/app.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function punch_out(Request $request)
    {
        $request->merge(['attendace_type' => 'out']);
        return $this->punch_in_out($request);
    }

    /**
     * Punch In/Out with auto-detection and day-change cleanup.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function punch_in_out(Request $request)
    {
        try {
            $employee = Auth::user();

            if (!$employee) {
                return $this->sendError('Unauthorized.', [], [], 401);
            }

            // 1. Validate required parameters
            $validator = Validator::make($request->all(), [
                'attendace_type' => 'required|in:in,out',
                'attendance_date' => 'sometimes|date_format:Y-m-d',
                'punch_in_time' => 'sometimes|date_format:h:i A',
                'punch_out_time' => 'sometimes|date_format:h:i A',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors()->all(), [], 422);
            }

            // 2. Strict parameter check (no extra keys allowed)
            $allowedKeys = ['attendace_type', 'attendance_date', 'punch_in_time', 'punch_out_time', 'remark', 'latitude', 'longitude'];
            $requestKeys = array_keys($request->all());
            $extraKeys = array_diff($requestKeys, $allowedKeys);
            if (!empty($extraKeys)) {
                return $this->sendError('Invalid parameters: ' . implode(', ', $extraKeys), [], [], 422);
            }

            $now = Carbon::now();
            // Force server date and time to prevent manual manipulation via API/Postman
            $attendanceDate = $now->format('Y-m-d');
            $punchInTime24 = $now->format('H:i:s');
            $requestedType = $request->attendace_type;

            // Find the VERY LATEST record for this employee
            $lastRecord = Attendance::where('employee_id', $employee->id)
                ->orderBy('attendance_date', 'desc')
                ->orderBy('punch_in_time', 'desc')
                ->first();

            $nextAction = $requestedType;

            // 4. Handle Daily Cleanup: Auto-punch out from previous days if forgotten
            $this->autoPunchOutPreviousDays($employee, $attendanceDate);

            // Re-fetch last record after potential auto-punch out to ensure logic consistency
            $lastRecord = Attendance::where('employee_id', $employee->id)
                ->orderBy('attendance_date', 'desc')
                ->orderBy('punch_in_time', 'desc')
                ->first();

            if ($nextAction == 'in' && $lastRecord && $lastRecord->attendace_type == 'in' && $lastRecord->attendance_date == $attendanceDate) {
                // Prevent duplicate Punch-In for same day if already in
                return $this->sendError('You are already Punched-In for today.', [], [], 422);
            }

            if ($nextAction == 'out' && (!$lastRecord || $lastRecord->attendace_type == 'out')) {
                return $this->sendError('Cannot Punch-Out without a valid Punch-In.', [], [], 422);
            }

            // Get current shift logic
            $shift_id = $employee->employmentDetail?->shift ?? 0;
            if ($shift_id == 0) {
                $defaultShift = Shift::where('company_id', $employee->company_id)->first();
                $shift_id = $defaultShift ? $defaultShift->id : 0;
            }

            // Prepare current punch data
            $data = [
                'company_id' => $employee->company_id,
                'employee_id' => $employee->id,
                'shift_id' => $shift_id,
                'attendance_date' => $attendanceDate,
                'create_date' => $now->toDateTimeString(),
                'punch_in_time' => $punchInTime24, // Store in DB in 24-hour format
                'attendace_type' => $nextAction,
                'remark' => $request->remark ?? 'Punched via API',
                'status' => 'active',
                'records_source' => 'api',
                'requested_data' => json_encode($request->all()),
                'device_serial' => $request->header('User-Agent'),
                'device_ip' => $request->ip(),
                'created_by' => $employee->id,
            ];

            $attendance = Attendance::create($data);

            // Fetch formatting data for response
            $formattedData = [
                'id' => $attendance->id,
                'employee_id' => $attendance->employee_id,
                'attendance_date' => $attendance->attendance_date,
                'formatted_attendance_date' => Carbon::parse($attendance->attendance_date)->format('d-m-Y'),
                'attendace_type' => $attendance->attendace_type,
                'punch_in_time' => null,
                'punch_out_time' => null,
                'remark' => $attendance->remark,
            ];

            if ($nextAction == 'in') {
                $formattedData['punch_in_time'] = Carbon::parse($attendance->punch_in_time)->format('h:i A');
            } else {
                $formattedData['punch_out_time'] = Carbon::parse($attendance->punch_in_time)->format('h:i A');
                // Find corresponding punch-in for today/session to show in response
                $inRecord = Attendance::where('employee_id', $employee->id)
                    ->where('attendance_date', $attendance->attendance_date)
                    ->where('attendace_type', 'in')
                    ->orderBy('punch_in_time', 'desc')
                    ->first();
                if ($inRecord) {
                    $formattedData['punch_in_time'] = Carbon::parse($inRecord->punch_in_time)->format('h:i A');
                }
            }

            $statusText = ($nextAction == 'in') ? 'Punch-In' : 'Punch-Out';
            return $this->sendResponse($formattedData, $statusText . ' Successfully.');

        } catch (\Exception $e) {
            return $this->sendError('Error recording attendance.', [$e->getMessage()], [], 500);
        }
    }

    /**
     * Get Punch History for the logged-in employee.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function punch_history(Request $request)
    {
        try {
            $employee = Auth::user();

            if (!$employee) {
                return $this->sendError('Unauthorized.', [], [], 401);
            }

            $history = Attendance::where('employee_id', $employee->id)
                ->orderBy('attendance_date', 'desc')
                ->orderBy('punch_in_time', 'desc')
                ->paginate($request->get('limit', 20));

            $history->getCollection()->transform(function ($item) {
                $punchTime = Carbon::parse($item->punch_in_time)->format('h:i A');
                $item->formatted_attendance_date = Carbon::parse($item->attendance_date)->format('d-m-Y');

                if ($item->attendace_type == 'in') {
                    $item->punch_in_time = $punchTime;
                    $item->punch_out_time = null;
                } else {
                    $item->punch_out_time = $punchTime;
                    // For history, we don't necessarily want to lookup the 'in' record for every row as it's expensive
                    // but we can at least ensure the fields exist.
                    $item->punch_in_time = null;
                }
                return $item;
            });

            return $this->sendResponse($history, 'Punch history retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', [$e->getMessage()], [], 500);
        }
    }

    /**
     * Get today's active punch status for the logged-in employee.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function active_punch_in_records(Request $request)
    {
        try {
            $employee = Auth::user();

            if (!$employee) {
                return $this->sendError('Unauthorized.', [], [], 401);
            }

            $today = Carbon::today()->format('Y-m-d');

            // Handle Daily Cleanup: Auto-punch out from previous days if forgotten
            $this->autoPunchOutPreviousDays($employee, $today);

            // Get the last punch of the day to determine current status
            $lastPunch = Attendance::where('employee_id', $employee->id)
                ->where('attendance_date', $today)
                ->orderBy('punch_in_time', 'desc')
                ->first();

            if ($lastPunch) {
                $punchTime = Carbon::parse($lastPunch->punch_in_time)->format('h:i A');
                $lastPunch->formatted_attendance_date = Carbon::parse($lastPunch->attendance_date)->format('d-m-Y');

                if ($lastPunch->attendace_type == 'in') {
                    $lastPunch->punch_in_time = $punchTime;
                    $lastPunch->punch_out_time = null;
                } else {
                    $lastPunch->punch_out_time = $punchTime;
                    // Find corresponding punch-in
                    $inRecord = Attendance::where('employee_id', $employee->id)
                        ->where('attendance_date', $today)
                        ->where('attendace_type', 'in')
                        ->orderBy('punch_in_time', 'desc')
                        ->first();
                    $lastPunch->punch_in_time = $inRecord ? Carbon::parse($inRecord->punch_in_time)->format('h:i A') : null;
                }
            }

            return $this->sendResponse($lastPunch, 'Active punch records status retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', [$e->getMessage()], [], 500);
        }
    }

    /**
     * Automatically punch out open sessions from previous days.
     * Per user request: At 12 AM (new day), old sessions should be auto-closed.
     * 
     * @param $employee
     * @param $currentDate
     */
    private function autoPunchOutPreviousDays($employee, $currentDate)
    {
        // Disabled per user request (Auto punch out should not happen)
        return false;

        $lastRecord = Attendance::where('employee_id', $employee->id)
            ->orderBy('attendance_date', 'desc')
            ->orderBy('punch_in_time', 'desc')
            ->first();

        if ($lastRecord && $lastRecord->attendace_type == 'in' && $lastRecord->attendance_date < $currentDate) {
            $shift = Shift::find($lastRecord->shift_id);
            
            // Only auto-punch out if the shift has an explicit auto_punch_out time set
            if (!$shift || empty($shift->auto_punch_out) || $shift->auto_punch_out === '00:00:00') {
                return false;
            }

            $punchOutTime = $shift->auto_punch_out;

            Attendance::create([
                'company_id' => $employee->company_id,
                'employee_id' => $employee->id,
                'shift_id' => $lastRecord->shift_id,
                'attendance_date' => $lastRecord->attendance_date,
                'create_date' => $lastRecord->attendance_date . ' ' . $punchOutTime,
                'punch_in_time' => $punchOutTime,
                'attendace_type' => 'out',
                'remark' => 'Auto punch-out (Day change cleanup)',
                'status' => 'active',
                'records_source' => 'api',
                'created_by' => $employee->id,
            ]);
            return true;
        }
        return false;
    }
}
