<?php

namespace App\Http\Controllers\software;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeWiseSalaryDetail;
use App\Models\Holiday;
use App\Models\LeaveApplication;
use App\Models\LeaveType;
use App\Models\Shift;
use Carbon\Carbon;
use DateInterval;
use DatePeriod;
use DateTime;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AttendanceReportExport;

class AttendanceReportController extends Controller
{
    public $modules = [];
    public function __construct()
    {
        parent::__construct();

        $this->modules = [
            'title' => 'Attendance Report',
            'folder_path' => 'software.modules.reports.attendance-report',
            'route' => 'attendance-report',
            // 'table_name' => (new ())->getTable(),
            'permisstion_prefix' => 'attendance-report',
            'module_name' => 'Attendance Report',
            'company_id' => ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails : null,

        ];
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $modules = $this->modules;
        $modules['authLoginUserDetail'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails : null;
        // Set company_id to pre-select in dropdown (user can still change it)
        $modules['company_id'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails?->company_id : null;
        $modules['parent_type_id'] = ($this->authenticateLoginUserDetails?->parent_type_id) ? $this->authenticateLoginUserDetails?->parent_type_id : null;
        $loginUserId = ($modules['authLoginUserDetail'] && $modules['authLoginUserDetail']?->id) ? $modules['authLoginUserDetail']?->id : null;
        if (count(config('constants.permissions'))) {
            foreach (config('constants.permissions') as $key => $value) {
                $modules[$value . '_permission'] = (isset($modules['company_id']) && !$modules['company_id']) ? true : Gate::check('hasPermission', [$value, $modules['module_name']]);
            }
        }
        if (!$modules['view_permission']) {
            if (isset($request) && $request->ajax()) {
                return $this->sendError('Unauthorized', [], [], 403);
            }
            abort(403, 'Unauthorized');
        }
        // return $modules;
        View::share('modules', $modules);
        try {


            $columns = [];

            if ($modules['company_id']) {
                // array_unshift($columns, (object)['data' => "company_name", 'name' => 'company_name', 'td_label' => 'Company Name', 'className' =>  '']);
                $columns = array_filter($columns, function ($col) {
                    return $col->name !== 'company_id';
                });
                $columns = array_values($columns);
            }
            View::share("columns", $columns);


            return view($modules['folder_path'] . '.index');
        } catch (\Exception $e) {
            return Redirect::route('software.dashboard')->withErrors($e->getMessage());
        }
    }

    public function getAttendanceReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => ['required', Rule::exists((new Company())->getTable(), 'id')],
            'employee_id' => ['nullable', Rule::exists((new Employee())->getTable(), 'id')],
            'followup_date' => ['nullable'],
            'get_calculation_only' => ['nullable', 'in:true,false'],
            'branch_id' => ['nullable', Rule::exists((new Branch())->getTable(), 'id')],
            'department_id' => ['nullable', 'required_if:get_calculation_only,true', Rule::exists((new Department())->getTable(), 'id')],
            'month' => ['required_if:get_calculation_only,true'],
            'year' => ['required_if:get_calculation_only,true'],
        ]);

        if ($validator->fails()) {
            return $this->sendError(
                $validator->messages()->first(),
                $validator->messages(),
                [],
                401
            );
        }

        $companyId = $request->company_id;
        $employeeId = $request->employee_id;
        $getCalculationOnly = filter_var($request->get_calculation_only, FILTER_VALIDATE_BOOLEAN);

        if ($getCalculationOnly) {
            return $this->generateCalculationReport($request, $companyId, $employeeId);
        }

        return $this->generateDetailedReport($request, $companyId, $employeeId);
    }

    public function musterIndex(Request $request)
    {
        $modules = $this->modules;
        $modules['authLoginUserDetail'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails : null;
        $modules['company_id'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails?->company_id : null;
        $modules['parent_type_id'] = ($this->authenticateLoginUserDetails?->parent_type_id) ? $this->authenticateLoginUserDetails?->parent_type_id : null;

        if (count(config('constants.permissions'))) {
            foreach (config('constants.permissions') as $key => $value) {
                $modules[$value . '_permission'] = (isset($modules['company_id']) && !$modules['company_id']) ? true : Gate::check('hasPermission', [$value, $modules['module_name']]);
            }
        }
        if (!$modules['view_permission']) {
            if (isset($request) && $request->ajax()) {
                return $this->sendError('Unauthorized', [], [], 403);
            }
            abort(403, 'Unauthorized');
        }

        View::share('modules', $modules);
        try {
            return view($modules['folder_path'] . '.muster');
        } catch (\Exception $e) {
            return Redirect::route('software.dashboard')->withErrors($e->getMessage());
        }
    }

    public function getMusterReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => ['required', Rule::exists((new Company())->getTable(), 'id')],
            'employee_id' => ['nullable', Rule::exists((new Employee())->getTable(), 'id')],
            'followup_date' => ['nullable'],
            'branch_id' => ['nullable', Rule::exists((new Branch())->getTable(), 'id')],
            'department_id' => ['nullable', Rule::exists((new Department())->getTable(), 'id')],
        ]);

        if ($validator->fails()) {
            return $this->sendError(
                $validator->messages()->first(),
                $validator->messages(),
                [],
                401
            );
        }

        $companyId = $request->company_id;
        $employeeId = $request->employee_id;

        //Reuse generateDetailedReport logic but return muster HTML
        //Actually, generateDetailedReport likely prepares data then calls generateAttendanceTableHTML
        //I should duplicate the data preparation part of generateDetailedReport here or refactor it.
        //Let's check generateDetailedReport content first.
        return $this->generateDetailedReport($request, $companyId, $employeeId, true);
    }



    /**
     * Show the form for creating a new resource.
     */

    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Export attendance report to Excel
     */
    public function exportExcel(Request $request)
    {
        $modules = $this->modules;
        $modules['authLoginUserDetail'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails : null;
        $modules['company_id'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails?->company_id : null;
        $modules['parent_type_id'] = ($this->authenticateLoginUserDetails?->parent_type_id) ? $this->authenticateLoginUserDetails?->parent_type_id : null;
        $loginUserId = ($modules['authLoginUserDetail'] && $modules['authLoginUserDetail']?->id) ? $modules['authLoginUserDetail']?->id : null;

        if (count(config('constants.permissions'))) {
            foreach (config('constants.permissions') as $key => $value) {
                $modules[$value . '_permission'] = (isset($modules['company_id']) && !$modules['company_id']) ? true : Gate::check('hasPermission', [$value, $modules['module_name']]);
            }
        }

        if (!$modules['excel_permission']) {
            if (isset($request) && $request->ajax()) {
                return $this->sendError('Unauthorized', [], [], 403);
            }
            abort(403, 'Unauthorized');
        }

        $validator = Validator::make($request->all(), [
            'company_id' => ['required', Rule::exists((new Company())->getTable(), 'id')],
            'employee_id' => ['nullable', Rule::exists((new Employee())->getTable(), 'id')],
            'followup_date' => ['required'],
            'branch_id' => ['nullable', Rule::exists((new Branch())->getTable(), 'id')],
            'department_id' => ['nullable', Rule::exists((new Department())->getTable(), 'id')],
        ]);

        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator)->withInput();
        }

        try {
            // Get the report data
            $reportData = $this->getAttendanceReport($request);
            $data = json_decode($reportData->getContent(), true);

            $fileName = 'Attendance-Report-' . date('Ymd-His') . '.xlsx';

            $isMuster = filter_var($request->is_muster, FILTER_VALIDATE_BOOLEAN);

            if ($isMuster) {
                // For Muster Report Export
                return Excel::download(
                    new \App\Exports\AttendanceMusterReportExport($data, $request->all(), $this->authenticateLoginUserDetails, $modules),
                    'Muster-Report-' . date('Ymd-His') . '.xlsx'
                );
            }

            return Excel::download(
                new AttendanceReportExport($data, $request->all(), $this->authenticateLoginUserDetails, $modules),
                $fileName
            );
        } catch (\Exception $e) {
            return Redirect::back()->withErrors(['error' => 'Export failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Convert time string (HH:MM:SS) to minutes
     */
    private function timeToMinutes($timeString)
    {
        if (empty($timeString)) {
            return 0;
        }

        // Extract time part if datetime format
        $timeString = $this->extractTime($timeString);

        $parts = explode(':', $timeString);
        $hours = isset($parts[0]) ? (int) $parts[0] : 0;
        $minutes = isset($parts[1]) ? (int) $parts[1] : 0;
        $seconds = isset($parts[2]) ? (int) $parts[2] : 0;

        return ($hours * 60) + $minutes + floor($seconds / 60);
    }

    /**
     * Extract time part from datetime string
     */
    private function extractTime($datetime)
    {
        if (empty($datetime)) {
            return '';
        }

        try {
            // Use Carbon to safely extract time, works for both time strings and datetimes
            return Carbon::parse($datetime)->format('H:i:s');
        } catch (\Exception $e) {
            // Fallback to old logic if Carbon fails
            if (strpos($datetime, ' ') !== false) {
                $parts = explode(' ', $datetime);
                return $parts[1] ?? $parts[0];
            }
            return $datetime;
        }
    }

    /**
     * Get punch details modal HTML for a specific attendance record
     */
    public function getPunchDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => ['required', Rule::exists((new Employee())->getTable(), 'id')],
            'attendance_date' => ['required', 'date'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->messages()->first(),
                'html' => ''
            ], 422);
        }

        $employeeId = $request->employee_id;
        $attendanceDate = $request->attendance_date;

        // Get employee
        $employee = Employee::find($employeeId);
        if (!$employee) {
            return response()->json([
                'status' => false,
                'message' => 'Employee not found.',
                'html' => ''
            ], 404);
        }

        // Get attendance records for the date
        $attendances = Attendance::where('employee_id', $employeeId)
            ->whereDate('attendance_date', $attendanceDate)
            ->orderBy('punch_in_time')
            ->get();

        if ($attendances->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No attendance records found for this date.',
                'html' => ''
            ], 404);
        }

        // Format punches
        $punches = [];
        foreach ($attendances as $attendance) {
            $time = $this->extractTime($attendance->punch_in_time);
            if (empty($time)) {
                continue; // Skip if time is empty
            }
            $punches[] = [
                'type' => strtolower($attendance->attendace_type ?? 'in'),
                'time' => $time,
                'full_time' => $attendance->punch_in_time
            ];
        }

        if (empty($punches)) {
            return response()->json([
                'status' => false,
                'message' => 'No valid punch records found for this date.',
                'html' => ''
            ], 404);
        }

        // Sort punches by time
        usort($punches, function ($a, $b) {
            return strcmp($a['time'], $b['time']);
        });

        // Calculate IN-OUT pairs
        $pairs = [];
        $sumMinutes = 0;
        for ($i = 0; $i < count($punches); $i++) {
            if (
                strtolower($punches[$i]['type']) === 'in' &&
                isset($punches[$i + 1]) &&
                strtolower($punches[$i + 1]['type']) === 'out'
            ) {
                $inTime = $punches[$i]['time'];
                $outTime = $punches[$i + 1]['time'];
                $total = $this->calculateMinutesDiff($inTime, $outTime);
                $sumMinutes += $total;
                $pairs[] = [
                    'in' => $inTime,
                    'out' => $outTime,
                    'total' => $total
                ];
                $i++; // skip OUT
            }
        }

        // Format date for display
        $formattedDate = Carbon::parse($attendanceDate)->format('d/m/Y');

        // Generate HTML
        $html = '<div style="margin-bottom: 15px;">';
        $html .= '<strong>Employee:</strong> ' . htmlspecialchars($employee->full_name) . '<br>';
        $html .= '<strong>Date:</strong> ' . $formattedDate;
        $html .= '</div>';

        $html .= '<table class="modal-table">';
        $html .= '<tr><th>Sr No</th><th>Type</th><th>Time</th></tr>';

        foreach ($punches as $index => $punch) {
            $typeLabel = strtolower($punch['type']) === 'in'
                ? '<span style="color: #43a047; font-weight: bold;">IN</span>'
                : '<span style="color: #e53935; font-weight: bold;">OUT</span>';

            $html .= '<tr>';
            $html .= '<td>' . ($index + 1) . '</td>';
            $html .= '<td>' . $typeLabel . '</td>';
            $html .= '<td>' . htmlspecialchars($punch['time']) . '</td>';
            $html .= '</tr>';
        }

        $html .= '</table>';

        // Show IN-OUT pairs summary
        if (count($pairs) > 0) {
            $html .= '<div style="margin-top: 20px;">';
            $html .= '<h4 style="margin-bottom: 10px;">Time Summary</h4>';
            $html .= '<table class="modal-table">';
            $html .= '<tr><th>IN Time</th><th>OUT Time</th><th>Duration</th></tr>';

            foreach ($pairs as $pair) {
                $duration = $this->formatMinutesToTime($pair['total']);
                $html .= '<tr>';
                $html .= '<td><span style="color: #43a047;">' . htmlspecialchars($pair['in']) . '</span></td>';
                $html .= '<td><span style="color: #e53935;">' . htmlspecialchars($pair['out']) . '</span></td>';
                $html .= '<td>' . $duration . '</td>';
                $html .= '</tr>';
            }

            $totalDuration = $this->formatMinutesToTime($sumMinutes);
            $html .= '<tr style="font-weight:bold; background-color: #f0f0f0;">';
            $html .= '<td colspan="2">Total Working Time</td>';
            $html .= '<td>' . $totalDuration . '</td>';
            $html .= '</tr>';
            $html .= '</table>';
            $html .= '</div>';
        }

        return response()->json([
            'status' => true,
            'message' => 'Punch details retrieved successfully.',
            'html' => $html
        ]);
    }

    /**
     * Calculate minutes difference between two times (HH:MM:SS format)
     */
    private function calculateMinutesDiff($inTime, $outTime)
    {
        if (empty($inTime) || empty($outTime)) {
            return 0;
        }

        try {
            // Parse time strings (HH:MM:SS format)
            $inParts = explode(':', $inTime);
            $outParts = explode(':', $outTime);

            if (count($inParts) < 2 || count($outParts) < 2) {
                return 0;
            }

            $inMinutes = (int) ($inParts[0] ?? 0) * 60 + (int) ($inParts[1] ?? 0);
            $outMinutes = (int) ($outParts[0] ?? 0) * 60 + (int) ($outParts[1] ?? 0);

            // If out time is before in time, assume next day (add 24 hours)
            if ($outMinutes < $inMinutes) {
                $outMinutes += 24 * 60;
            }

            return $outMinutes - $inMinutes;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Format minutes to HH:MM:SS
     */
    private function formatMinutesToTime($minutes)
    {
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;
        $secs = 0;
        return sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
    }

    /**
     * Generate attendance table HTML
     */
    private function generateAttendanceTableHTML($employees, $startDate, $weekOffMap = [], $holidayDates = [], $leaveDates = [], $pendingLeaveDates = [])
    {
        $daysInMonth = $startDate->daysInMonth;
        $monthYear = $startDate->format('F Y');
        $today = Carbon::today();

        // Generate days header
        $daysHead = '';
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $daysHead .= '<th>' . str_pad($day, 2, '0', STR_PAD_LEFT) . '</th>';
        }

        // Generate Right Header (Summary Columns)
        $rightHead = '';
        $rightHead .= '<tr>';
        $rightHead .= '<th class="working-hours-col" style="min-width: 60px;">Present</th>';
        $rightHead .= '<th class="working-hours-col" style="min-width: 70px;">Week Off</th>';
        $rightHead .= '<th class="working-hours-col" style="min-width: 70px;">Half Days</th>';
        $rightHead .= '<th class="working-hours-col" style="min-width: 60px;">Leave</th>';
        $rightHead .= '<th class="working-hours-col" style="min-width: 60px;">Absent</th>';
        $rightHead .= '<th class="working-hours-col" style="min-width: 60px;">Holiday</th>';
        $rightHead .= '<th class="working-hours-col" style="min-width: 110px;">Days Compensated</th>';
        $rightHead .= '<th class="working-hours-col" style="min-width: 110px;">Total Working Hour</th>';
        $rightHead .= '</tr>';

        // Generate table rows
        $leftBody = '';
        $scrollableBody = '';
        $rightBody = '';

        foreach ($employees as $index => $employee) {
            // Convert to array if object
            $emp = is_array($employee) ? $employee : (array) $employee;

            $employeeId = $emp['id'] ?? $emp['employee_id'] ?? '';
            $employeeName = $emp['name'] ?? '';
            $attendances = $emp['attendances'] ?? [];
            $totalWorkingHours = $emp['total_working_hours'] ?? '00:00:00';

            // Get employee week off days
            $employeeWeekOffDays = $weekOffMap[$employeeId] ?? [];

            // Ensure attendances is an array
            if ($attendances instanceof \Illuminate\Support\Collection) {
                $attendances = $attendances->toArray();
            } elseif (!is_array($attendances)) {
                $attendances = (array) $attendances;
            }

            // Left column (Sr No, Employee Name)
            $leftBody .= '<tr>';
            $leftBody .= '<td>' . ($index + 1) . '</td>';
            $leftBody .= '<td class="employee-col">' . $employeeName . '</td>';
            $leftBody .= '</tr>';

            // Create attendance map by day
            $attendanceMap = [];
            foreach ($attendances as $att) {
                // Convert to array if object
                $attData = is_array($att) ? $att : (array) $att;

                $attendanceDate = $attData['attendance_date'] ?? null;
                if ($attendanceDate) {
                    $dateParts = explode('-', $attendanceDate);
                    if (count($dateParts) >= 3) {
                        $day = (int) $dateParts[2];
                        if ($day >= 1 && $day <= 31) {
                            $attendanceMap[$day] = $attData;
                        }
                    }
                }
            }

            // Generate day cells
            $dayCells = '';
            $calculatedAbsent = 0;
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $att = $attendanceMap[$day] ?? null;
                $currentDate = Carbon::createFromDate($startDate->year, $startDate->month, $day);
                $dateStr = $currentDate->format('Y-m-d');
                $dayOfWeek = $currentDate->dayOfWeek;

                if (!$att) {
                    // No attendance record - check for holiday, leave, week off, or absent

                    // Priority: Holiday > Leave > Week Off > Future > Absent

                    // 1. Check if it's a holiday
                    if (isset($holidayDates[$dateStr])) {
                        $content = '<span class="holiday">H</span>';
                        $cellClass = 'holiday-cell';
                        $dayCells .= '<td class="' . $cellClass . '">' . $content . '</td>';
                        continue;
                    }

                    // 2. Check if employee has approved leave on this date
                    if (isset($leaveDates[$employeeId][$dateStr])) {
                        $leaveRecords = $leaveDates[$employeeId][$dateStr];
                        $halfDay = null;
                        foreach ($leaveRecords as $leaveInfo) {
                            if (isset($leaveInfo['halfday_fullday']) && $leaveInfo['halfday_fullday'] === 'halfday') {
                                $halfDay = $leaveInfo['firsthalf_secondhalf'] ?? null;
                                break;
                            }
                        }

                        if ($halfDay === 'firsthalf' || $halfDay === 'secondhalf') {
                            $content = '<span class="leave purple-cell">' . ($halfDay === 'firsthalf' ? 'FH' : 'SH') . '</span>';
                            $cellClass = 'leave-half-cell';
                        } else {
                            $shortName = $leaveRecords[0]['short_name'] ?? 'L';
                            $content = '<span class="leave full-leave-cell">' . $shortName . '</span>';
                            $cellClass = 'leave-cell';
                        }
                        $dayCells .= '<td class="' . $cellClass . '">' . $content . '</td>';
                        continue;
                    }

                    // 2b. Check if employee has pending leave on this date (show differently)
                    // if (isset($pendingLeaveDates[$employeeId][$dateStr])) {
                    //     $leaveRecords = $pendingLeaveDates[$employeeId][$dateStr];
                    //     $halfDay = null;

                    //     foreach ($leaveRecords as $leaveInfo) {
                    //         if (isset($leaveInfo['halfday_fullday']) && $leaveInfo['halfday_fullday'] === 'halfday') {
                    //             $halfDay = $leaveInfo['firsthalf_secondhalf'] ?? null;
                    //             break;
                    //         }
                    //     }

                    //     // Show pending leave with different styling
                    //     if ($halfDay === 'firsthalf' || $halfDay === 'secondhalf') {
                    //         $content = '<span class="leave pending-leave purple-cell">' . ($halfDay === 'firsthalf' ? 'FH' : 'SH') . '<br><small style="font-size:9px;">P</small></span>';
                    //         $cellClass = 'leave-half-cell pending-leave-cell';
                    //     } else {
                    //         $content = '<span class="leave pending-leave full-leave-cell">L<br><small style="font-size:9px;">P</small></span>';
                    //         $cellClass = 'leave-cell pending-leave-cell';
                    //     }
                    //     $dayCells .= '<td class="' . $cellClass . '">' . $content . '</td>';
                    //     continue;
                    // }

                    // 3. Check if it's a week off day for this employee
                    if (in_array($dayOfWeek, $employeeWeekOffDays)) {
                        $content = '<span class="week-off">WO</span>';
                        $cellClass = 'week-off-cell';
                        $dayCells .= '<td class="' . $cellClass . '">' . $content . '</td>';
                        continue;
                    }

                    // 4. Check if it's a future date
                    if ($currentDate->gt($today)) {
                        $content = '<span class="weekend">-</span>';
                        $cellClass = '';
                        $dayCells .= '<td class="' . $cellClass . '">' . $content . '</td>';
                        continue;
                    }

                    // 5. No attendance record and not holiday/leave/week off = Absent
                    // STRICT RULE: No punches = Absent
                    $dayCells .= '<td class="absent-cell"><span class="absent">A</span></td>';
                    $calculatedAbsent++;
                    continue;
                }

                // Ensure $att is an array
                $att = is_array($att) ? $att : (array) $att;

                $cellClass = '';
                $content = '-';
                $attType = strtolower($att['attendance_type'] ?? '');

                // Initialize $isLate to false
                $isLate = false;
                // Handle late punch
                $isLate = !empty($att['is_late']) && $att['is_late'];

                // Handle different attendance types - PRIORITY ADJUSTMENT
                $punches = $att['all_punches'] ?? [];
                $punchCount = count($punches);
                $isToday = $currentDate->isToday();
                $isPast = $currentDate->isPast();

                // Logic: 
                // 1. Future -> '-' (Already handled)
                // 2. WeekOff/Holiday -> 'WO'/'H' (unless worked?)
                // 3. Absent (0 punches) -> 'A'
                // 4. Missing Punch (Punches > 0 AND (Absent OR Odd Punches on Past Date)) -> 'MP'
                // 5. Present/HalfDay -> 'P'/'HD'

                // Check for Missing Punch Condition specifically
                // Condition A: Status is 'absent' but punches exist
                // Condition B: Past date and punch count is odd (1, 3, 5...)

                $forceMissingPunch = false;
                if ($isPast && !$isToday && $punchCount > 0 && ($punchCount % 2 != 0)) {
                    $forceMissingPunch = true;
                }

                // CHECK WORKING HOURS for "Short Work" logic similar to Salary Calculation
                // If worked < Half Day (approx 4 hours), treat as Absent (A) instead of MP/P
                $wh = $att['working_hours'] ?? '00:00:00';
                $whMinutes = $this->timeToMinutes($wh);

                // Use Shift-specific Half Day Limit if available, else default 240
                $halfDayLimitMinutes = $att['shift_half_day_minutes'] ?? 240;

                $isShortWork = ($punchCount > 0 && $whMinutes > 0 && $whMinutes < $halfDayLimitMinutes);

                if ($attType === '-' || $attType === '') {
                    $content = '<span class="weekend">-</span>';
                    $cellClass = '';
                } elseif ($attType === 'week_off') {
                    // Check if they actually worked on week off? (week_off_working handles this)
                    $content = '<span class="week-off">WO</span>';
                    $cellClass = 'week-off-cell';
                } elseif ($attType === 'holiday') {
                    $content = '<span class="holiday">H</span>';
                    $cellClass = 'holiday-cell';
                } elseif (($attType === 'absent' || $forceMissingPunch || $isShortWork) && !in_array($attType, ['half_day', 'present', 'week_off_working'])) {

                    // STRICT LOGIC PER USER REQUEST:
                    // MP (Miss Punch) = ONLY Odd Punches (Missing Out)
                    // A (Absent) = No Punches OR Short Duration (Working < Half Day)

                    if ($forceMissingPunch) {
                        // Odd Punches -> MP - Show only first IN, last OUT (if exists)
                        $allPunches = $att['all_punches'] ?? [];
                        if ($allPunches instanceof \Illuminate\Support\Collection) {
                            $allPunches = $allPunches->toArray();
                        }
                        $allPunches = is_array($allPunches) ? array_values($allPunches) : [];

                        $firstPunchIn = null;
                        $lastPunchOut = null;
                        $punchCount = count($allPunches);
                        $hasMultiplePunches = $punchCount > 1;

                        // Find first IN punch
                        foreach ($allPunches as $punch) {
                            $punch = (array) $punch;
                            $type = strtolower($punch['type'] ?? '');
                            if ($type === 'in' && !$firstPunchIn) {
                                $firstPunchIn = $punch['time'] ?? null;
                                break;
                            }
                        }

                        // Find last OUT punch (if exists)
                        for ($i = count($allPunches) - 1; $i >= 0; $i--) {
                            $punch = (array) $allPunches[$i];
                            $type = strtolower($punch['type'] ?? '');
                            if ($type === 'out') {
                                $lastPunchOut = $punch['time'] ?? null;
                                break;
                            }
                        }

                        $timeInDisp = $firstPunchIn ? $this->formatTimeForDisplay($firstPunchIn) : '-';
                        $timeOutDisp = $lastPunchOut ? $this->formatTimeForDisplay($lastPunchOut) : '-';
                        $totalDisp = $att['working_hours'] ?? '00:00';

                        $content = '<div class="present" style="cursor: pointer;" onclick="showPunchDetails(' . $employeeId . ', \'' . $currentDate->format('Y-m-d') . '\')">';
                        $content .= '<span class="present-label missing-punch" style="background-color: #ff9800; color: white; padding: 2px 4px; border-radius: 4px; font-size: 10px;">MP</span>';
                        $content .= '<div style="font-size: 10px; line-height: 1.2; margin-top: 2px;">';

                        if ($timeInDisp && $timeInDisp !== '-') {
                            $content .= '<span style="color: green; font-weight: bold;">In: ' . $timeInDisp . '</span>';
                        }

                        if ($timeOutDisp && $timeOutDisp !== '-') {
                            $content .= '<br><span style="color: red;">Out: ' . $timeOutDisp . '</span>';
                        }

                        if ($totalDisp && $totalDisp !== '00:00' && $totalDisp !== '00:00:00') {
                            $parts = explode(':', $totalDisp);
                            $totalHoursFormatted = $parts[0] . ':' . $parts[1];
                            $content .= '<br><span style="font-weight: bold;">Total: ' . $totalHoursFormatted . '</span>';
                        }

                        if ($hasMultiplePunches) {
                            $content .= '<br><small style="font-size: 8px; color: #666; text-decoration: underline;">Click for all punches</small>';
                        }

                        $content .= '</div>';
                        $content .= '</div>';
                        $cellClass = 'missing-punch-cell';
                        $calculatedAbsent++;
                    } elseif ($isShortWork) {
                        // Worked but < Half Day Limit -> Absent (A) - Show only first IN, last OUT
                        $allPunches = $att['all_punches'] ?? [];
                        if ($allPunches instanceof \Illuminate\Support\Collection) {
                            $allPunches = $allPunches->toArray();
                        }
                        $allPunches = is_array($allPunches) ? array_values($allPunches) : [];

                        $firstPunchIn = null;
                        $lastPunchOut = null;
                        $punchCount = count($allPunches);
                        $hasMultiplePunches = $punchCount > 1;

                        // Find first IN punch
                        foreach ($allPunches as $punch) {
                            $punch = (array) $punch;
                            $type = strtolower($punch['type'] ?? '');
                            if ($type === 'in' && !$firstPunchIn) {
                                $firstPunchIn = $punch['time'] ?? null;
                                break;
                            }
                        }

                        // Find last OUT punch (if exists)
                        for ($i = count($allPunches) - 1; $i >= 0; $i--) {
                            $punch = (array) $allPunches[$i];
                            $type = strtolower($punch['type'] ?? '');
                            if ($type === 'out') {
                                $lastPunchOut = $punch['time'] ?? null;
                                break;
                            }
                        }

                        $timeInDisp = $firstPunchIn ? $this->formatTimeForDisplay($firstPunchIn) : '-';
                        $timeOutDisp = $lastPunchOut ? $this->formatTimeForDisplay($lastPunchOut) : '-';
                        $totalDisp = $att['working_hours'] ?? '00:00';

                        $content = '<div class="present" style="cursor: pointer;" onclick="showPunchDetails(' . $employeeId . ', \'' . $currentDate->format('Y-m-d') . '\')">';
                        $content .= '<span class="present-label absent" style="background-color: #ffcccc; color: #cc0000; padding: 2px 4px; border-radius: 4px; font-size: 10px;">A</span>';
                        $content .= '<div style="font-size: 10px; line-height: 1.2; margin-top: 2px;">';

                        if ($timeInDisp && $timeInDisp !== '-') {
                            $content .= '<span style="color: green; font-weight: bold;">In: ' . $timeInDisp . '</span>';
                        }

                        if ($timeOutDisp && $timeOutDisp !== '-') {
                            $content .= '<br><span style="color: red;">Out: ' . $timeOutDisp . '</span>';
                        }

                        if ($totalDisp && $totalDisp !== '00:00' && $totalDisp !== '00:00:00') {
                            $parts = explode(':', $totalDisp);
                            $totalHoursFormatted = $parts[0] . ':' . $parts[1];
                            $content .= '<br><span style="font-weight: bold;">Total: ' . $totalHoursFormatted . '</span>';
                        }

                        if ($hasMultiplePunches) {
                            $content .= '<br><small style="font-size: 8px; color: #666; text-decoration: underline;">Click for all punches</small>';
                        }

                        $content .= '</div>';
                        $content .= '</div>';
                        $cellClass = 'absent-cell';
                        $calculatedAbsent++;
                    } else {
                        // Truly Absent (No punches)
                        $content = '<span class="absent">A</span>';
                        $cellClass = 'absent-cell';
                        $calculatedAbsent++;
                    }
                } elseif ($attType === 'leave') {
                    // ... (leave logic remains same) ...
                    $halfDay = $att['half_day'] ?? '';
                    $leaveStatus = strtolower($att['leave_status'] ?? 'approved');
                    $attendanceDate = $att['attendance_date'] ?? '';

                    $hasPendingLeave = isset($pendingLeaveDates[$employeeId][$attendanceDate]);
                    $isPending = ($leaveStatus === 'pending' || $hasPendingLeave);

                    if ($halfDay === 'firsthalf' || $halfDay === 'secondhalf') {
                        $label = ($halfDay === 'firsthalf' ? 'FH' : 'SH');
                        if ($isPending)
                            $label .= '<br><small style="font-size:9px;">P</small>';
                        $content = '<span class="leave ' . ($isPending ? 'pending-leave' : '') . ' purple-cell">' . $label . '</span>';
                        $cellClass = 'leave-half-cell ' . ($isPending ? 'pending-leave-cell' : '');
                    } else {
                        $label = $att['leave_type_short_name'] ?? 'L';
                        if ($isPending)
                            $label .= '<br><small style="font-size:9px;">P</small>';
                        $content = '<span class="leave ' . ($isPending ? 'pending-leave' : '') . ' full-leave-cell">' . $label . '</span>';
                        $cellClass = 'leave-cell ' . ($isPending ? 'pending-leave-cell' : '');
                    }
                } elseif ($attType === 'present' || $attType === 'half_day' || $attType === 'week_off_working') {
                    $statusLabel = 'P';
                    $labelClass = 'present-label';
                    if ($attType === 'half_day') {
                        $statusLabel = 'HD';
                    }
                    if ($attType === 'week_off_working') {
                        $statusLabel = 'WOW';
                        $labelClass .= ' week-off';
                    }

                    // Extract first punch_in and last punch_out from all_punches
                    $allPunches = $att['all_punches'] ?? [];
                    if ($allPunches instanceof \Illuminate\Support\Collection) {
                        $allPunches = $allPunches->toArray();
                    }
                    $allPunches = is_array($allPunches) ? array_values($allPunches) : [];

                    $firstPunchIn = null;
                    $lastPunchOut = null;
                    $punchCount = count($allPunches);
                    $hasMultiplePunches = $punchCount > 2; // More than first IN and last OUT

                    // Find first IN punch
                    foreach ($allPunches as $punch) {
                        $punch = (array) $punch;
                        $type = strtolower($punch['type'] ?? '');
                        if ($type === 'in' && !$firstPunchIn) {
                            $firstPunchIn = $punch['time'] ?? null;
                            break;
                        }
                    }

                    // Find last OUT punch
                    for ($i = count($allPunches) - 1; $i >= 0; $i--) {
                        $punch = (array) $allPunches[$i];
                        $type = strtolower($punch['type'] ?? '');
                        if ($type === 'out') {
                            $lastPunchOut = $punch['time'] ?? null;
                            break;
                        }
                    }

                    // Format times for display
                    $timeInDisp = $firstPunchIn ? $this->formatTimeForDisplay($firstPunchIn) : '-';
                    $timeOutDisp = $lastPunchOut ? $this->formatTimeForDisplay($lastPunchOut) : '-';
                    $totalDisp = $att['working_hours'] ?? '00:00';

                    // Generate content
                    $content = '<div class="present" style="cursor: pointer;" onclick="showPunchDetails(' . $employeeId . ', \'' . $currentDate->format('Y-m-d') . '\')">';
                    $content .= '<span class="' . $labelClass . '">' . $statusLabel . '</span>';

                    // Times - Show only first IN and last OUT
                    $content .= '<div style="font-size: 10px; line-height: 1.2; margin-top: 2px;">';

                    // Show first punch IN
                    if ($timeInDisp && $timeInDisp !== '-') {
                        $content .= '<span style="color: green; font-weight: bold;">In: ' . $timeInDisp . '</span>';
                    } else {
                        $content .= '<span style="color: green; font-weight: bold;">In: -</span>';
                    }

                    // Show last punch OUT only if it exists
                    if ($timeOutDisp && $timeOutDisp !== '-') {
                        $content .= '<br><span style="color: red;">Out: ' . $timeOutDisp . '</span>';
                    }

                    // Show total working hours (keep as is)
                    $totalHoursFormatted = $totalDisp;
                    if ($totalDisp && $totalDisp !== '00:00' && $totalDisp !== '00:00:00') {
                        if (strpos($totalDisp, ':') !== false) {
                            $parts = explode(':', $totalDisp);
                            $totalHoursFormatted = $parts[0] . ':' . $parts[1];
                        }
                        $content .= '<br><span style="font-weight: bold;">Total: ' . $totalHoursFormatted . '</span>';
                    } else {
                        $content .= '<br><span style="font-weight: bold;">Total: 00:00</span>';
                    }

                    // Show indicator if multiple punches exist
                    if ($hasMultiplePunches) {
                        $content .= '<br><small style="font-size: 8px; color: #666; text-decoration: underline;">Click for all punches</small>';
                    }

                    $content .= '</div>';
                    $content .= '</div>';

                    $cellClass = $attType === 'half_day' ? 'leave-half-cell' : ($attType === 'week_off_working' ? 'week-off-working-cell' : 'present-cell');
                }

                $dayCells .= '<td class="' . $cellClass . '">' . $content . '</td>';
            }

            $scrollableBody .= '<tr>' . $dayCells . '</tr>';

            // Right column (Working Time)
            // Right column (Summary Stats)
            // Extract summary stats from employee data 
            // (Note: $emp is the processed array from processDetailedEmployeeData)
            $totalPresent = $emp['total_present_days'] ?? 0;
            $totalHalfDays = $emp['total_half_days'] ?? 0;
            $paidLeave = $emp['paid_leave_days'] ?? 0;
            $unpaidLeave = $emp['unpaid_leave_days'] ?? 0;
            $totalLeave = $paidLeave + $unpaidLeave;

            // Calculate Absent approx if not set? 
            // processDetailedEmployeeData doesn't strictly sum total_absent_days, but let's count 'absent' types in attendances if needed
            // Actually, we can count it from the attendance array we have here
            // But wait, $emp['attendances'] has 'absent' status for MP/A.
            // Use calculated absent count from the loop above
            $absentCount = $calculatedAbsent;

            $holidayCount = $emp['paid_holiday_days'] ?? 0;
            $weekOffCount = ($emp['paid_week_off_days'] ?? 0) + ($emp['week_off_working_days'] ?? 0);
            $daysCompensated = $emp['payable_days'] ?? 0;

            $rightBody .= '<tr>';
            $rightBody .= '<td class="working-hours-col present-cell">' . $totalPresent . '</td>';
            $rightBody .= '<td class="working-hours-col">' . $weekOffCount . '</td>';
            $rightBody .= '<td class="working-hours-col">' . $totalHalfDays . '</td>';
            $rightBody .= '<td class="working-hours-col leave-cell">' . $totalLeave . '</td>';
            $rightBody .= '<td class="working-hours-col absent-cell">' . $absentCount . '</td>';
            $rightBody .= '<td class="working-hours-col holiday-cell">' . $holidayCount . '</td>';
            $rightBody .= '<td class="working-hours-col" style="font-weight:bold;">' . $daysCompensated . '</td>';
            $rightBody .= '<td class="working-hours-col" style="font-weight:bold;">' . $totalWorkingHours . '</td>';
            $rightBody .= '</tr>';
        }

        // Build complete HTML structure
        $html = [
            'daysHead' => $daysHead,
            'rightHead' => $rightHead,
            'leftBody' => $leftBody,
            'scrollableBody' => $scrollableBody,
            'rightBody' => $rightBody,
            'monthYear' => $monthYear
        ];

        return $html;
    }

    private function generatePunchContent($att, $employeeId, $isLate = false)
    {
        $att = (array) $att;
        $wh = $att['working_hours'] ?? '00:00:00';
        $punches = $att['all_punches'] ?? [];

        if ($punches instanceof \Illuminate\Support\Collection) {
            $punches = $punches->toArray();
        }

        $punches = is_array($punches) ? array_values($punches) : array_values((array) $punches);

        $content = '';
        $pairs = [];
        $currentIn = null;

        // 1. Try to pair punches from the raw list
        foreach ($punches as $punch) {
            $punch = (array) $punch;
            $type = strtolower($punch['type'] ?? '');
            $time = $punch['time'] ?? '';

            if ($type === 'in') {
                if ($currentIn !== null) {
                    // Previous IN had no OUT, push as single
                    $pairs[] = ['in' => $currentIn, 'out' => null];
                }
                $currentIn = $time;
            } elseif ($type === 'out') {
                if ($currentIn !== null) {
                    $pairs[] = ['in' => $currentIn, 'out' => $time];
                    $currentIn = null;
                } else {
                    // Out without IN (should not happen normally but handle it)
                    $pairs[] = ['in' => null, 'out' => $time];
                }
            }
        }
        if ($currentIn !== null) {
            $pairs[] = ['in' => $currentIn, 'out' => null];
        }

        // Fix for Calculated Half Day OUT times:
        // If we have a single 'IN' punch (or last punch is IN) and a calculated 'time_out' exists in $att, use it.
        // This ensures Half Day calculated times are shown instead of just '-'
        if (!empty($pairs)) {
            $lastIndex = count($pairs) - 1;
            if (empty($pairs[$lastIndex]['out']) && !empty($att['time_out']) && $att['time_out'] !== '-') {
                $pairs[$lastIndex]['out'] = $att['time_out'];
            }
        }

        // 2. Fallback: If no pairs found via logic but time_in/time_out exist (e.g. Half Day explicit with missing punches)
        if (empty($pairs) && (!empty($att['time_in']) || !empty($att['time_out']))) {
            $pairs[] = ['in' => $att['time_in'], 'out' => $att['time_out']];
        }

        // 3. Generate HTML
        foreach ($pairs as $index => $pair) {
            $inTime = $pair['in'];
            $outTime = $pair['out'];

            // In Entry
            $content .= '<div class="punch-row" style="line-height: 1.2; font-size: 11px;">';
            if ($inTime) {
                // For first pair, apply Late style if needed
                $style = ($index === 0 && $isLate) ? 'color: green; font-weight: bold;' : 'color: green;';
                $content .= '<span style="font-weight: 500;">In : </span><span class="time-in" style="' . $style . '">' . htmlspecialchars($this->formatTimeForDisplay($inTime)) . '</span>';
            } else {
                $content .= '<span style="font-weight: 500;">In : </span> -';
            }
            $content .= '</div>';

            // Out Entry
            $content .= '<div class="punch-row" style="line-height: 1.2; font-size: 11px; margin-bottom: 3px;">';
            if ($outTime) {
                $content .= '<span style="font-weight: 500;">Out : </span><span class="time-out" style="color: red;">' . htmlspecialchars($this->formatTimeForDisplay($outTime)) . '</span>';
            } else {
                $content .= '<span style="font-weight: 500;">Out : </span> -';
            }
            $content .= '</div>';
        }

        if ($wh && $wh !== '00:00:00' && $wh !== '00:00') {
            $whParts = explode(':', $wh);
            $displayWH = $whParts[0] . ':' . $whParts[1];
            $content .= '<div style="margin-top: 2px; border-top: 1px dashed #ccc; padding-top: 2px;"><span class="working-hours" style="font-size: 0.85em; color: #666; font-weight: bold;">Total : ' . $displayWH . '</span></div>';
        }

        return $content;
    }

    private function formatTimeForDisplay($time)
    {
        if (!$time || $time === '-')
            return '-';

        // If time contains space (datetime), extract time part
        if (strpos($time, ' ') !== false) {
            $parts = explode(' ', $time);
            $time = $parts[1] ?? $parts[0];
        }

        // Format to HH:MM if it's HH:MM:SS
        if (preg_match('/^(\d{1,2}):(\d{2}):(\d{2})$/', $time, $matches)) {
            return $matches[1] . ':' . $matches[2];
        }

        // If already in HH:MM format, return as is
        if (preg_match('/^\d{1,2}:\d{2}$/', $time)) {
            return $time;
        }

        return $time;
    }

    /**
     * Export attendance report as Print (PDF/Print View)
     */
    public function exportPrint(Request $request)
    {
        $modules = $this->modules;
        $modules['authLoginUserDetail'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails : null;
        $modules['company_id'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails?->company_id : null;
        $modules['parent_type_id'] = ($this->authenticateLoginUserDetails?->parent_type_id) ? $this->authenticateLoginUserDetails?->parent_type_id : null;
        $loginUserId = ($modules['authLoginUserDetail'] && $modules['authLoginUserDetail']?->id) ? $modules['authLoginUserDetail']?->id : null;

        if (count(config('constants.permissions'))) {
            foreach (config('constants.permissions') as $key => $value) {
                $modules[$value . '_permission'] = (isset($modules['company_id']) && !$modules['company_id']) ? true : Gate::check('hasPermission', [$value, $modules['module_name']]);
            }
        }

        if (!$modules['view_permission']) {
            if (isset($request) && $request->ajax()) {
                return $this->sendError('Unauthorized', [], [], 403);
            }
            abort(403, 'Unauthorized');
        }

        $validator = Validator::make($request->all(), [
            'company_id' => ['required', Rule::exists((new Company())->getTable(), 'id')],
            'employee_id' => ['nullable', Rule::exists((new Employee())->getTable(), 'id')],
            'followup_date' => ['required'],
            'branch_id' => ['nullable', Rule::exists((new Branch())->getTable(), 'id')],
            'department_id' => ['nullable', Rule::exists((new Department())->getTable(), 'id')],
        ]);

        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator)->withInput();
        }

        try {
            // Get the report data using the same method
            $reportData = $this->getAttendanceReport($request);
            $data = json_decode($reportData->getContent(), true);

            // Parse monthYear to create Carbon object for the view
            $monthYearStr = $data['monthYear'] ?? '';
            $monthYear = null;
            if ($monthYearStr) {
                try {
                    $monthYear = Carbon::createFromFormat('F Y', $monthYearStr);
                } catch (\Exception $e) {
                    $monthYear = Carbon::now();
                }
            } else {
                $monthYear = Carbon::now();
            }

            // Convert employees array to objects for the view
            $employees = [];
            foreach ($data['employees'] ?? [] as $emp) {
                $empObj = (object) $emp;
                // Convert attendances array to objects
                $attendances = [];
                foreach ($emp['attendances'] ?? [] as $att) {
                    $attendances[] = is_array($att) ? (object) $att : $att;
                }
                $empObj->attendances = $attendances;
                $employees[] = $empObj;
            }

            View::share('modules', $modules);
            View::share('employees', $employees);
            View::share('monthYear', $monthYear);
            View::share('daysInMonth', $data['daysInMonth'] ?? 0);

            $isMuster = filter_var($request->is_muster, FILTER_VALIDATE_BOOLEAN);

            if ($isMuster) {
                return view($modules['folder_path'] . '.muster_print');
            }

            return view($modules['folder_path'] . '.detailed_print');
        } catch (\Exception $e) {
            return Redirect::back()->withErrors(['error' => 'Export failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Generate Calculation Only Report
     */
    private function generateCalculationReport(Request $request, $companyId, $employeeId)
    {
        $month = (int) $request->month;
        $year = (int) $request->year;

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        $today = Carbon::today();

        // Fetch employees
        $employees = Employee::when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->when($employeeId, fn($q) => $q->where('id', $employeeId))
            ->when($request->filled('department_id'), function ($q) use ($request) {
                return $q->whereHas('employmentDetail', function ($q2) use ($request) {
                    $q2->where('department_id', $request->department_id);
                });
            })
            ->select('id', 'full_name', 'employee_code')
            ->get();

        // Fetch week offs
        $employeeWeekOffs = EmployeeWiseSalaryDetail::where('company_id', $companyId)
            ->when($employeeId, fn($q) => $q->where('employee_id', $employeeId))
            ->pluck('week_off', 'employee_id')->toArray();

        $dayMap = [
            'sun' => 0,
            'sunday' => 0,
            'mon' => 1,
            'monday' => 1,
            'tue' => 2,
            'tuesday' => 2,
            'wed' => 3,
            'wednesday' => 3,
            'thu' => 4,
            'thursday' => 4,
            'fri' => 5,
            'friday' => 5,
            'sat' => 6,
            'saturday' => 6,
        ];

        $weekOffMap = [];
        foreach ($employeeWeekOffs as $empId => $woJson) {
            $woArray = json_decode($woJson, true);
            $weekOffMap[$empId] = [];
            if (is_array($woArray)) {
                foreach ($woArray as $wo) {
                    $woStr = strtolower(trim($wo));
                    if (isset($dayMap[$woStr])) {
                        $weekOffMap[$empId][] = $dayMap[$woStr];
                    }
                }
            }
        }

        // Fetch attendances
        $attendancesData = Attendance::whereIn('employee_id', $employees->pluck('id'))
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->get()
            ->groupBy(fn($item) => $item->employee_id);

        // Fetch leaves
        $leavesQuery = LeaveApplication::where('status', 'Approved')
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('fromdate_time', [$startDate, $endDate])
                    ->orWhereBetween('todate_time', [$startDate, $endDate])
                    ->orWhere(fn($q2) => $q2->where('fromdate_time', '<', $startDate)
                        ->where('todate_time', '>', $endDate));
            });

        // Check if employee_id column exists before using it
        $hasEmployeeIdColumn = Schema::hasColumn('leave_applications', 'employee_id');
        if ($hasEmployeeIdColumn) {
            $leavesQuery->whereIn('employee_id', $employees->pluck('id'));
        }

        $leaves = $leavesQuery->get();

        // Group by employee_id if column exists, otherwise group by a fallback
        if ($hasEmployeeIdColumn) {
            $leaves = $leaves->groupBy('employee_id');
        } else {
            // If column doesn't exist, create empty groups for each employee
            $leaves = collect();
            foreach ($employees as $employee) {
                $leaves->put($employee->id, collect());
            }
        }

        // Fetch holidays
        $holidays = Holiday::where('company_id', $companyId)
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('from_date', [$startDate, $endDate])
                    ->orWhereBetween('to_date', [$startDate, $endDate])
                    ->orWhere(fn($q2) => $q2->where('from_date', '<', $startDate)->where('to_date', '>', $endDate));
            })->get();

        $holidayDates = [];
        foreach ($holidays as $h) {
            $from = Carbon::parse($h->from_date)->startOfDay();
            $to = Carbon::parse($h->to_date)->startOfDay();
            for ($d = $from; $d->lte($to); $d->addDay()) {
                $holidayDates[$d->format('Y-m-d')] = $h->holiday_label;
            }
        }

        $data = [];

        foreach ($employees as $employee) {
            $records = [];
            $totalPresentDays = 0;
            $totalHalfDays = 0;
            $totalLeaveDays = 0;
            $totalWeekOff = 0;
            $weekOfSet = [];

            $employeeAttendances = $attendancesData->get($employee->id, collect());
            $employeeLeaves = $leaves->get($employee->id, collect());
            $employeeWeekOffDays = $weekOffMap[$employee->id] ?? [];

            // ✅ Calculate leave days
            $leaveDates = [];
            foreach ($employeeLeaves as $leave) {
                if ($leave->halfday_fullday === 'halfday') {
                    $totalHalfDays++;
                    $leaveDates[] = Carbon::parse($leave->fromdate_time)->format('Y-m-d');
                } else {
                    $from = Carbon::parse($leave->fromdate_time)->startOfDay();
                    $to = Carbon::parse($leave->todate_time)->startOfDay();
                    for ($d = $from; $d->lte($to); $d->addDay()) {
                        $leaveDates[] = $d->format('Y-m-d');
                    }
                }
            }
            $leaveDates = array_unique($leaveDates);
            $totalLeaveDays = count($leaveDates);

            // ✅ Loop through month for daily status
            for ($day = $startDate->copy(); $day->lte($endDate); $day->addDay()) {
                $dateStr = $day->format('Y-m-d');
                $weekOf = $day->copy()->startOfWeek()->format('d/m/Y');
                $weekOfSet[$weekOf] = true;

                if (in_array($dateStr, $leaveDates)) {
                    $status = $employeeLeaves->where('fromdate_time', '<=', $day)
                        ->where('todate_time', '>=', $day)->first()->halfday_fullday ?? 'leave';
                    $status = $status === 'halfday' ? 'half_day' : 'leave';
                } elseif ($employeeAttendances->where('attendance_date', $dateStr)->count() > 0) {
                    $status = 'present';
                    $totalPresentDays++;
                } elseif (isset($holidayDates[$dateStr])) {
                    $status = 'holiday';
                } elseif (in_array($day->dayOfWeek, $employeeWeekOffDays)) {
                    $status = 'week_off';
                    $totalWeekOff++;
                } else {
                    $status = $day->gt($today) ? '-' : 'absent';
                }

                $records[] = [
                    'date' => $dateStr,
                    'status' => $status,
                    'week_of' => $weekOf,
                ];
            }

            // ✅ Count only till today
            $totalDaysTillToday = $today->lt($endDate) ? $today->day : $startDate->daysInMonth;
            $totalPresentTillToday = 0;
            $totalLeaveTillToday = 0;
            $totalHolidaysTillToday = 0;
            $totalWeekOffTillToday = 0;

            foreach ($records as $rec) {
                $recDate = Carbon::parse($rec['date']);
                if ($recDate->lte($today)) {
                    switch ($rec['status']) {
                        case 'present':
                            $totalPresentTillToday++;
                            break;
                        case 'leave':
                            $totalLeaveTillToday++;
                            break;
                        case 'holiday':
                            $totalHolidaysTillToday++;
                            break;
                        case 'week_off':
                            $totalWeekOffTillToday++;
                            break;
                    }
                }
            }

            // ✅ Absent count till today
            $totalAbsents = $totalDaysTillToday - (
                $totalPresentTillToday +
                $totalLeaveTillToday +
                $totalHolidaysTillToday +
                $totalWeekOffTillToday +
                ($totalHalfDays * 0.5)
            );
            // Sandwich leave calculation
            $sandwichLeave = 0;
            $recordCount = count($records);
            for ($i = 0; $i < $recordCount; $i++) {
                if ($records[$i]['status'] === 'leave') {
                    $start = $i;
                    while ($start > 0 && in_array($records[$start - 1]['status'], ['week_off', 'holiday']))
                        $start--;
                    $end = $i;
                    while ($end < $recordCount - 1 && in_array($records[$end + 1]['status'], ['week_off', 'holiday']))
                        $end++;
                    $sandwichLeave += ($end - $start + 1);
                    $i = $end;
                }
            }

            $data[] = [
                'name' => '<b>' . $employee->employee_code . '</b> -' . $employee->proper_name,
                'attendances' => $records,
                'total_present_days' => $totalPresentDays,
                'total_half_days' => $totalHalfDays,
                'total_leave' => $totalLeaveDays + ($totalHalfDays * 0.5),
                'total_holidays' => count(array_filter(array_column($records, 'status'), fn($s) => $s === 'holiday')),
                'total_week_off' => $totalWeekOff,
                'total_absent_days' => max(0, $totalAbsents), // ✅ only till today
                'calculate_days' => count($weekOfSet),
                'sandwich_leave' => $sandwichLeave,
            ];
        }

        return response()->json([
            'employees' => $data,
            'monthYear' => $startDate->format('F Y'),
            'daysInMonth' => $startDate->daysInMonth,
        ]);
    }

    /**
     * Generate Detailed Report
     */
    private function generateDetailedReport(Request $request, $companyId, $employeeId, $isMuster = false)
    {
        // Normalize date range
        $dateRange = $request->followup_date;
        if (!$dateRange) {
            return response()->json(['error' => true, 'message' => 'Date range required'], 422);
        }

        $dateRange = str_replace([' to ', ' TO ', '–'], ' - ', $dateRange);
        $dates = explode(' - ', $dateRange);

        try {
            $startDate = Carbon::createFromFormat('d/m/Y', trim($dates[0]))->startOfDay();
            $endDate = Carbon::createFromFormat('d/m/Y', trim($dates[1]))->endOfDay();
        } catch (\Exception $e) {
            return response()->json(['error' => true, 'message' => 'Invalid date format'], 422);
        }

        // Fetch employees
        $employees = Employee::when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->when($employeeId, fn($q) => $q->where('id', $employeeId))
            ->when($request->filled('department_id'), function ($q) use ($request) {
                return $q->whereHas('employmentDetail', function ($q2) use ($request) {
                    $q2->where('department_id', $request->department_id);
                });
            })
            ->select('id', 'full_name', 'employee_code')
            ->get();

        // Employee week offs
        $employeeWeekOffs = EmployeeWiseSalaryDetail::where('company_id', $companyId)
            ->when($employeeId, fn($q) => $q->where('employee_id', $employeeId))
            ->pluck('week_off', 'employee_id')->toArray();

        $dayMap = [
            'sun' => 0,
            'sunday' => 0,
            'mon' => 1,
            'monday' => 1,
            'tue' => 2,
            'tuesday' => 2,
            'wed' => 3,
            'wednesday' => 3,
            'thu' => 4,
            'thursday' => 4,
            'fri' => 5,
            'friday' => 5,
            'sat' => 6,
            'saturday' => 6
        ];

        $weekOffMap = [];
        foreach ($employeeWeekOffs as $empId => $woJson) {
            $woArray = json_decode($woJson, true);
            $weekOffMap[$empId] = [];
            if (is_array($woArray)) {
                foreach ($woArray as $wo) {
                    $woStr = strtolower(trim($wo));
                    if (isset($dayMap[$woStr]))
                        $weekOffMap[$empId][] = $dayMap[$woStr];
                }
            }
        }

        // Holidays
        $holidays = Holiday::where('company_id', $companyId)
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('from_date', [$startDate, $endDate])
                    ->orWhereBetween('to_date', [$startDate, $endDate])
                    ->orWhere(fn($q2) => $q2->where('from_date', '<', $startDate)->where('to_date', '>', $endDate));
            })->get();

        $holidayDates = [];
        foreach ($holidays as $h) {
            $from = Carbon::parse($h->from_date)->startOfDay();
            $to = Carbon::parse($h->to_date)->startOfDay();
            for ($d = $from; $d->lte($to); $d->addDay()) {
                $holidayDates[$d->format('Y-m-d')] = $h->holiday_label;
            }
        }

        // Leaves
        $leavesQuery = LeaveApplication::where('company_id', $companyId)
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('fromdate_time', [$startDate, $endDate])
                    ->orWhereBetween('todate_time', [$startDate, $endDate])
                    ->orWhere(fn($q2) => $q2->where('fromdate_time', '<', $startDate)->where('todate_time', '>', $endDate));
            });

        // Check if employee_id column exists before using it
        $hasEmployeeIdColumn = Schema::hasColumn('leave_applications', 'employee_id');
        if ($hasEmployeeIdColumn && $employeeId) {
            $leavesQuery->where('employee_id', $employeeId);
        }

        $leaves = $leavesQuery->with('leave_type')->get();

        // Filter by employee_id in memory if column doesn't exist but employeeId is provided
        if (!$hasEmployeeIdColumn && $employeeId) {
            $leaves = $leaves->filter(function ($leave) use ($employeeId) {
                // Try to get employee_id from relationship or attribute
                return isset($leave->employee_id) && $leave->employee_id == $employeeId;
            });
        }

        $leaveDates = [];
        $pendingLeaveDates = [];

        foreach ($leaves as $leave) {
            $from = Carbon::parse($leave->fromdate_time)->startOfDay();
            $to = Carbon::parse($leave->todate_time ?? $leave->fromdate_time)->startOfDay();
            $leaveStatus = strtolower($leave->status ?? 'pending');

            // Get employee_id safely - if column doesn't exist, we can't filter by employee
            $leaveEmployeeId = $hasEmployeeIdColumn ? ($leave->employee_id ?? null) : null;

            // If column doesn't exist, we can't determine which employee the leave belongs to
            // Skip processing leaves if we can't identify the employee
            if (!$hasEmployeeIdColumn) {
                continue;
            }

            // If employeeId filter is provided but leave doesn't match, skip
            if ($employeeId && $leaveEmployeeId != $employeeId) {
                continue;
            }

            if (!$leaveEmployeeId) {
                continue; // Skip if employee_id is null
            }

            for ($d = $from; $d->lte($to); $d->addDay()) {
                $dateKey = $d->format('Y-m-d');
                $leaveData = [
                    'type' => $leave->leave_type_id,
                    'reason' => $leave->leave_reason,
                    'short_name' => $leave->leave_type->sort_name ?? 'L',
                    'halfday_fullday' => $leave->halfday_fullday,
                    'firsthalf_secondhalf' => $leave->firsthalf_secondhalf,
                    'payment_mode' => $leave->leave_type->mode ?? 0,
                    'status' => $leaveStatus,
                ];

                if ($leaveStatus === 'approved') {
                    $leaveDates[$leaveEmployeeId][$dateKey][] = $leaveData;
                } else {
                    $pendingLeaveDates[$leaveEmployeeId][$dateKey][] = $leaveData;
                }
            }
        }

        $data = [];

        foreach ($employees as $employee) {
            $data[] = $this->processDetailedEmployeeData($employee, $startDate, $endDate, $weekOffMap, $holidayDates, $leaveDates);
        }

        // Generate HTML table on controller side
        if ($isMuster) {
            $html = $this->generateMusterHTML($data, $startDate, $weekOffMap, $holidayDates, $leaveDates, $pendingLeaveDates);
        } else {
            $html = $this->generateAttendanceTableHTML($data, $startDate, $weekOffMap, $holidayDates, $leaveDates, $pendingLeaveDates);
        }

        return response()->json([
            'employees' => $data,
            'monthYear' => $startDate->format('F Y'),
            'daysInMonth' => $startDate->daysInMonth,
            'html' => $html,
        ]);
    }

    /**
     * Process detailed attendance for a single employee
     */
    private function processDetailedEmployeeData($employee, $startDate, $endDate, $weekOffMap, $holidayDates, $leaveDates)
    {
        $today = Carbon::today();

        // Fetch attendance with shift data
        $attendancesRaw = Attendance::where('employee_id', $employee->id)
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->with('shift')
            ->select('attendance_date', 'attendace_type', 'punch_in_time', 'shift_id')
            ->orderBy('attendance_date')
            ->orderBy('punch_in_time')
            ->get();

        $attendancesByDate = $attendancesRaw->groupBy('attendance_date');
        $attendances = collect();
        $employeeWeekOffDays = $weekOffMap[$employee->id] ?? [];

        // Initialize counters
        $totalPresentDays = 0;
        $totalHalfDays = 0;
        $paidLeaveDays = 0;
        $unpaidLeaveDays = 0;
        $paidHolidayDays = 0;
        $paidWeekOffDays = 0;
        $weekOffWorkingDays = 0;
        $totalAbsentDays = 0;

        // Fetch Salary Detail for OT Flag
        $salaryDetail = \App\Models\EmployeeWiseSalaryDetail::where('employee_id', $employee->id)->latest()->first();
        $isOtEnabled = $salaryDetail && in_array($salaryDetail->overtime, ['yes', '1', 1, true]);
        $usedGraceDay = false;

        for ($day = $startDate->copy(); $day <= $endDate; $day->addDay()) {
            $dateStr = $day->format('Y-m-d');
            $records = $attendancesByDate->get($dateStr, collect());

            $shift = null;
            if ($records->isNotEmpty()) {
                $firstRecord = $records->first();
                if ($firstRecord->shift_id && $firstRecord->shift) {
                    $shift = $firstRecord->shift;
                }
            }

            $allPunches = $records->map(function ($r) {
                $time = $r->punch_in_time;
                if (strpos($time, ' ') !== false) {
                    $time = explode(' ', $time)[1] ?? $time;
                }
                return [
                    'type' => strtolower($r->attendace_type),
                    'time' => $time,
                    'full_time' => $r->punch_in_time
                ];
            })->sortBy('time')->values();

            $totalPunchMinutes = 0;
            for ($i = 0; $i < count($allPunches); $i++) {
                if (
                    strtolower($allPunches[$i]['type']) === 'in' &&
                    isset($allPunches[$i + 1]) &&
                    strtolower($allPunches[$i + 1]['type']) === 'out'
                ) {
                    $inTime = new DateTime($allPunches[$i]['time']);
                    $outTime = new DateTime($allPunches[$i + 1]['time']);
                    $diff = $outTime->diff($inTime);
                    $minutes = ($diff->h * 60) + $diff->i + floor($diff->s / 60);
                    $totalPunchMinutes += $minutes;
                    $i++;
                }
            }

            $workedMinutes = $totalPunchMinutes;
            if ($shift && $shift->breaking_hour) {
                $breakMinutes = $this->timeToMinutes($shift->breaking_hour);
                $workedMinutes = max(0, $workedMinutes - $breakMinutes);
            }

            $hours = floor($workedMinutes / 60);
            $minutes = $workedMinutes % 60;
            $workingHours = sprintf('%02d:%02d:%02d', $hours, $minutes, 0);

            $inRecords = $records->filter(fn($r) => strtolower($r->attendace_type) === 'in')->sortBy('punch_in_time');
            $outRecords = $records->filter(fn($r) => strtolower($r->attendace_type) === 'out')->sortByDesc('punch_in_time');

            $inRecord = $inRecords->first();
            $outRecord = $outRecords->first();

            $timeIn = $inRecord ? $this->extractTime($inRecord->punch_in_time) : null;
            $timeOut = $outRecord ? $this->extractTime($outRecord->punch_in_time) : null;

            $allInTimes = $inRecords->map(fn($r) => $this->extractTime($r->punch_in_time))->filter()->values()->toArray();
            $allOutTimes = $outRecords->map(fn($r) => $this->extractTime($r->punch_in_time))->filter()->values()->toArray();

            // 1. Holiday
            if (isset($holidayDates[$dateStr])) {
                $paidHolidayDays++;
                $attendances->push((object) [
                    'attendance_date' => $dateStr,
                    'attendance_type' => 'holiday',
                    'time_in' => $timeIn,
                    'time_out' => $timeOut,
                    'all_in_times' => $allInTimes,
                    'all_out_times' => $allOutTimes,
                    'working_hours' => '00:00:00',
                    'total_punch_minutes' => 0,
                    'worked_minutes' => 0,
                    'all_punches' => $allPunches,
                    'punch_count' => count($allPunches)
                ]);
                continue;
            }

            // 2. Leave
            if (isset($leaveDates[$employee->id][$dateStr])) {
                $leaveRecords = $leaveDates[$employee->id][$dateStr];
                $halfDay = null;
                $isPaidLeave = false;
                foreach ($leaveRecords as $leaveInfo) {
                    if ($leaveInfo['halfday_fullday'] === 'halfday') {
                        $halfDay = $leaveInfo['firsthalf_secondhalf'];
                    }
                    if (($leaveInfo['payment_mode'] ?? 0) == 1) {
                        $isPaidLeave = true;
                    }
                }

                if ($isPaidLeave) {
                    if ($halfDay)
                        $paidLeaveDays += 0.5;
                    else
                        $paidLeaveDays++;
                } else {
                    if ($halfDay)
                        $unpaidLeaveDays += 0.5;
                    else
                        $unpaidLeaveDays++;
                }

                $attendances->push((object) [
                    'attendance_date' => $dateStr,
                    'attendance_type' => 'leave',
                    'leave_type' => $leaveRecords[0]['type'],
                    'leave_reason' => $leaveRecords[0]['reason'],
                    'leave_type_short_name' => $leaveRecords[0]['short_name'] ?? 'L',
                    'half_day' => $halfDay,
                    'is_paid' => $isPaidLeave,
                    'leave_status' => 'approved',
                    'time_in' => $timeIn,
                    'time_out' => $timeOut,
                    'all_in_times' => $allInTimes,
                    'all_out_times' => $allOutTimes,
                    'working_hours' => $workingHours,
                    'total_punch_minutes' => $totalPunchMinutes,
                    'worked_minutes' => $workedMinutes,
                    'all_punches' => $allPunches,
                    'punch_count' => count($allPunches)
                ]);
                continue;
            }

            // 3. Week Off
            if (in_array($day->dayOfWeek, $employeeWeekOffDays)) {
                if ($workedMinutes > 0) {
                    $weekOffWorkingDays++;
                    $attendances->push((object) [
                        'attendance_date' => $dateStr,
                        'attendance_type' => 'week_off_working',
                        'time_in' => $timeIn,
                        'time_out' => $timeOut,
                        'all_in_times' => $allInTimes,
                        'all_out_times' => $allOutTimes,
                        'working_hours' => $workingHours,
                        'total_punch_minutes' => $totalPunchMinutes,
                        'worked_minutes' => $workedMinutes,
                        'all_punches' => $allPunches,
                        'punch_count' => count($allPunches)
                    ]);
                } else {
                    $paidWeekOffDays++;
                    $attendances->push((object) [
                        'attendance_date' => $dateStr,
                        'attendance_type' => 'week_off',
                        'time_in' => null,
                        'time_out' => null,
                        'all_in_times' => [],
                        'all_out_times' => [],
                        'working_hours' => '00:00:00',
                        'total_punch_minutes' => 0,
                        'worked_minutes' => 0,
                        'all_punches' => $allPunches,
                        'punch_count' => count($allPunches)
                    ]);
                }
                continue;
            }

            // 3.5 Check for explicit Half Day marking BEFORE checking IN/OUT
            // This handles records where attendace_type is set to 'Half Day' directly
            $explicitHalfDay = $records->contains(function ($r) {
                return in_array(strtolower($r->attendace_type), ['half_day', 'half day', 'hd']);
            });

            if ($explicitHalfDay) {
                // Extract times from ALL records for this day (to catch if they have separate IN/OUT punches)
                $allDayPunches = $records->pluck('punch_in_time')->map(function ($time) {
                    return $this->extractTime($time);
                })->filter()->sort()->values();

                $hdTimeIn = $allDayPunches->first();
                $hdTimeOut = $allDayPunches->count() > 1 ? $allDayPunches->last() : null;

                // FALLBACK: If Out Time is missing for Half Day, calculate it based on Shift Half Day Hours
                if (!$hdTimeOut && $hdTimeIn && $shift && $shift->half_day_hour) {
                    try {
                        $inCarbon = \Carbon\Carbon::createFromFormat('H:i:s', $hdTimeIn);
                        $halfDayDuration = explode(':', $shift->half_day_hour);
                        $h = (int) $halfDayDuration[0];
                        $m = (int) ($halfDayDuration[1] ?? 0);

                        $hdTimeOut = $inCarbon->addHours($h)->addMinutes($m)->format('H:i:s');

                        // Also add to all_out_times for consistency if needed, but 'generatePunchContent' uses 'time_out' fallback
                    } catch (\Exception $e) {
                        // Keep null if parsing fails
                    }
                }

                $hdAllInTimes = [];
                $hdAllOutTimes = [];

                // Collect all punch times
                foreach ($records as $r) {
                    $time = $this->extractTime($r->punch_in_time);
                    if ($time) {
                        // Naive classification: First is IN, Last is OUT, others? 
                        // Let's just put them in 'all_in_times' for display purposes for now
                        $hdAllInTimes[] = $time;
                    }
                }

                // If we found specific IN/OUT typed records, prefer those? 
                // But this block is triggered because explicit Half Day exists. 
                // Let's stick to First/Last logic for simple display of In/Out.

                $halfDayRecord = $records->first(function ($r) {
                    return in_array(strtolower($r->attendace_type), ['half_day', 'half day', 'hd']);
                });

                $isLate = false;

                if ($shift && $halfDayRecord && $shift->punch_in_minimum) {
                    try {
                        $shiftStartTimeStr = $this->extractTime($shift->punch_in_minimum);
                        $firstPunchTimeStr = $this->extractTime($halfDayRecord->punch_in_time);
                        $shiftStartMinutes = $this->timeToMinutes($shiftStartTimeStr);
                        if ($shift->in_out_grace_period) {
                            $shiftStartMinutes += (int) $shift->in_out_grace_period;
                        } elseif ($shift->grace_period) {
                            $shiftStartMinutes += (int) $shift->grace_period;
                        }
                        $firstPunchMinutes = $this->timeToMinutes($firstPunchTimeStr);
                        if ($firstPunchMinutes > $shiftStartMinutes) {
                            $isLate = true;
                        }
                    } catch (\Exception $e) {
                    }
                }

                $totalHalfDays++;
                $attendances->push((object) [
                    'attendance_date' => $dateStr,
                    'attendance_type' => 'half_day',
                    'time_in' => $hdTimeIn,
                    'time_out' => $hdTimeOut,
                    'all_in_times' => $hdAllInTimes,
                    'all_out_times' => $hdAllOutTimes,
                    'working_hours' => $workingHours,
                    'total_punch_minutes' => $totalPunchMinutes,
                    'worked_minutes' => $workedMinutes,
                    'all_punches' => $allPunches,
                    'punch_count' => count($allPunches),
                    'is_late' => $isLate,
                    'shift_half_day_minutes' => ($shift && $shift->half_day_hour) ? $this->timeToMinutes($shift->half_day_hour) : 240
                ]);
                continue;
            }

            // 4. Present/Half/Absent
            if ($inRecord || $outRecord) {
                // Late/Early Grace checks
                $isLateWithinOneHour = false;
                $isEarlyWithinOneHour = false;
                $isLateMoreThanOneHour = false;
                $isEarlyMoreThanOneHour = false;

                if ($shift) {
                    if ($shift->punch_in_minimum && $timeIn) {
                        try {
                            $shiftStartTimeStr = $this->extractTime($shift->punch_in_minimum);
                            $shiftStartMinutes = $this->timeToMinutes($shiftStartTimeStr);
                            $graceMinutes = (int) ($shift->in_out_grace_period ?? $shift->grace_period ?? 0);
                            $graceLimitMinutes = $shiftStartMinutes + $graceMinutes;

                            $firstPunchMinutes = $this->timeToMinutes($timeIn);
                            if ($firstPunchMinutes > $graceLimitMinutes) {
                                if ($firstPunchMinutes <= $graceLimitMinutes + 60) {
                                    $isLateWithinOneHour = true;
                                } else {
                                    $isLateMoreThanOneHour = true;
                                }
                            }
                        } catch (\Exception $e) {
                        }
                    }

                    if ($shift->punch_out && $timeOut) {
                        try {
                            $shiftEndTimeStr = $this->extractTime($shift->punch_out);
                            $lastPunchTimeStr = $this->extractTime($timeOut);
                            $shiftEndMinutes = $this->timeToMinutes($shiftEndTimeStr);
                            $graceMinutes = (int) ($shift->in_out_grace_period ?? 0);
                            $earlyGraceLimitMinutes = $shiftEndMinutes - $graceMinutes;

                            $lastPunchMinutes = $this->timeToMinutes($lastPunchTimeStr);
                            if ($lastPunchMinutes < $earlyGraceLimitMinutes) {
                                if ($lastPunchMinutes >= $earlyGraceLimitMinutes - 60) {
                                    $isEarlyWithinOneHour = true;
                                } else {
                                    $isEarlyMoreThanOneHour = true;
                                }
                            }
                        } catch (\Exception $e) {
                        }
                    }
                }

                $applyGrace = false;
                if (!$usedGraceDay && ($isLateWithinOneHour || $isEarlyWithinOneHour) && !$isLateMoreThanOneHour && !$isEarlyMoreThanOneHour) {
                    $applyGrace = true;
                    $usedGraceDay = true;
                }

                $isLate = false;
                $forceHalfDayDueToLate = false;
                if ($shift && $inRecord && $shift->punch_in_minimum && !$applyGrace) {
                    try {
                        $shiftStartTimeStr = $this->extractTime($shift->punch_in_minimum);
                        $firstPunchTimeStr = $this->extractTime($inRecord->punch_in_time);
                        $shiftStartMinutes = $this->timeToMinutes($shiftStartTimeStr);
                        if ($shift->in_out_grace_period) {
                            $shiftStartMinutes += (int) $shift->in_out_grace_period;
                        } elseif ($shift->grace_period) {
                            $shiftStartMinutes += (int) $shift->grace_period;
                        }
                        $firstPunchMinutes = $this->timeToMinutes($firstPunchTimeStr);
                        if ($firstPunchMinutes > $shiftStartMinutes) {
                            $isLate = true;
                            $forceHalfDayDueToLate = true;
                        }
                    } catch (\Exception $e) {
                    }
                }

                $presentDayMinutes = $shift ? $this->timeToMinutes($shift->present_day_hour ?? '08:00:00') : 480;
                $halfDayMinutes = $shift ? $this->timeToMinutes($shift->half_day_hour ?? '04:00:00') : 240;

                // Check if employee worked full day, half day, or less
                if (($workedMinutes >= $presentDayMinutes || $applyGrace) && !$forceHalfDayDueToLate) {
                    $totalPresentDays++;
                    $attendances->push((object) [
                        'attendance_date' => $dateStr,
                        'attendance_type' => 'present',
                        'time_in' => $timeIn,
                        'time_out' => $timeOut,
                        'all_in_times' => $allInTimes,
                        'all_out_times' => $allOutTimes,
                        'working_hours' => $workingHours,
                        'total_punch_minutes' => $totalPunchMinutes,
                        'worked_minutes' => $workedMinutes,
                        'all_punches' => $allPunches,
                        'punch_count' => count($allPunches),
                        'is_late' => $isLate,
                    ]);
                } elseif ($workedMinutes >= $halfDayMinutes) {
                    $totalHalfDays++;
                    $attendances->push((object) [
                        'attendance_date' => $dateStr,
                        'attendance_type' => 'half_day',
                        'time_in' => $timeIn,
                        'time_out' => $timeOut,
                        'all_in_times' => $allInTimes,
                        'all_out_times' => $allOutTimes,
                        'working_hours' => $workingHours,
                        'total_punch_minutes' => $totalPunchMinutes,
                        'worked_minutes' => $workedMinutes,
                        'all_punches' => $allPunches,
                        'punch_count' => count($allPunches),
                        'is_late' => $isLate,
                        'shift_half_day_minutes' => ($shift && $shift->half_day_hour) ? $this->timeToMinutes($shift->half_day_hour) : 240
                    ]);
                } else {
                    // 3. Absent (if no punches) or Present (Shift logic might dictate otherwise, but here we flag as absent/short)
                    // We push details so Report View can decide to show 'MP' or 'A'
                    $attendances->push((object) [
                        'attendance_date' => $dateStr,
                        'attendance_type' => 'absent',
                        'time_in' => $timeIn,
                        'time_out' => $timeOut,
                        'all_in_times' => $allInTimes,
                        'all_out_times' => $allOutTimes,
                        'working_hours' => $workingHours,
                        'total_punch_minutes' => $totalPunchMinutes,
                        'worked_minutes' => $workedMinutes,
                        'all_punches' => $allPunches,
                        'punch_count' => count($allPunches),
                        'is_late' => $isLate,
                        'shift_half_day_minutes' => ($shift && $shift->half_day_hour) ? $this->timeToMinutes($shift->half_day_hour) : 240
                    ]);
                }
                continue;
            }

            // 5. Future/Absent
            $attendances->push((object) [
                'attendance_date' => $dateStr,
                'attendance_type' => $day->gt($today) ? '-' : 'absent',
                'time_in' => null,
                'time_out' => null,
                'all_in_times' => [],
                'all_out_times' => [],
                'working_hours' => '00:00:00',
                'total_punch_minutes' => 0,
                'worked_minutes' => 0,
                'all_punches' => $allPunches,
                'punch_count' => count($allPunches)
            ]);
        }

        $attendances = $attendances->sortBy('attendance_date')->values();

        // Calculate Total Absent Days
        $totalAbsentDays = $attendances->filter(function ($att) {
            return $att->attendance_type === 'absent';
        })->count();

        $totalMinutes = $attendances->reduce(function ($carry, $item) {
            if (in_array($item->attendance_type, ['holiday', 'absent', 'week_off']))
                return $carry;
            return $carry + ($item->worked_minutes ?? $item->total_punch_minutes ?? 0);
        }, 0);

        $totalOvertimeMinutes = $attendances->reduce(function ($carry, $item) use ($isOtEnabled) {
            if (!$isOtEnabled)
                return 0; // If OT not enabled, return 0

            if (in_array($item->attendance_type, ['present', 'week_off_working', 'half_day'])) {
                $worked = $item->worked_minutes ?? 0;
                $standard = 480;
                if ($item->attendance_type == 'half_day')
                    $standard = 240;
                if ($worked > $standard)
                    return $carry + ($worked - $standard);
            }
            return $carry;
        }, 0);

        $totalWorkingHours = sprintf('%02d:%02d:%02d', floor($totalMinutes / 60), $totalMinutes % 60, 0);
        $totalOvertime = sprintf('%02d:%02d:%02d', floor($totalOvertimeMinutes / 60), $totalOvertimeMinutes % 60, 0);

        $payableDays = $totalPresentDays
            + ($totalHalfDays * 0.5)
            + $paidLeaveDays
            + $paidHolidayDays
            + $paidWeekOffDays
            + $weekOffWorkingDays;

        return [
            'id' => $employee->id,
            'employee_id' => $employee->id,
            'name' => '<b>' . $employee->employee_code . '</b> -' . $employee->proper_name,
            'attendances' => $attendances,
            'total_working_hours' => $totalWorkingHours,
            'total_present_days' => $totalPresentDays,
            'total_half_days' => $totalHalfDays,
            'paid_leave_days' => $paidLeaveDays,
            'unpaid_leave_days' => $unpaidLeaveDays,
            'paid_holiday_days' => $paidHolidayDays,
            'paid_week_off_days' => $paidWeekOffDays,
            'week_off_working_days' => $weekOffWorkingDays,
            'payable_days' => round($payableDays, 2),
            'total_overtime' => $totalOvertime,
            'total_absent_days' => $totalAbsentDays,
        ];
    }

    /**
     * Generate Muster Report HTML
     */
    private function generateMusterHTML($processedEmployees, $startDate, $weekOffMap = [], $holidayDates = [], $leaveDates = [], $pendingLeaveDates = [])
    {
        $daysInMonth = $startDate->daysInMonth;
        $monthYear = $startDate->format('F Y');
        $today = Carbon::today();

        // Generate days header
        $daysHead = '';
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $daysHead .= '<th style="min-width: 35px; width: 35px; padding: 2px; text-align: center;">' . str_pad($day, 2, '0', STR_PAD_LEFT) . '</th>';
        }

        // Generate table rows
        $leftBody = '';
        $scrollableBody = '';
        $rightBody = '';

        $totalSum = [
            'P' => 0,
            'A' => 0,
            'PL' => 0,
            'SL' => 0,
            'DL' => 0,
            'C-off' => 0,
            'LWP' => 0,
            'H' => 0,
            'WO' => 0,
            'Total' => 0,
        ];

        foreach ($processedEmployees as $index => $employeeData) {
            $employeeName = $employeeData['name'] ?? '';
            $attendances = $employeeData['attendances'] ?? collect();

            // Map attendances by date for easy lookup
            $attendanceMap = [];
            foreach ($attendances as $att) {
                // $att is an object
                $date = $att->attendance_date;
                $dateParts = explode('-', $date);
                if (count($dateParts) >= 3) {
                    $day = (int) $dateParts[2];
                    $attendanceMap[$day] = $att;
                }
            }
            $leftBody .= '<tr>';
            $leftBody .= '<td>' . ($index + 1) . '</td>';
            $leftBody .= '<td class="employee-col">' . $employeeName . '</td>';
            $leftBody .= '</tr>';

            // Generate day cells
            $dayCells = '';

            $summary = [
                'P' => 0, // Present
                'A' => 0, // Absent
                'PL' => 0,
                'SL' => 0,
                'DL' => 0,
                'C-off' => 0,
                'LWP' => 0,
                'H' => 0, // Holiday
                'WO' => 0, // Week Off
                'HD' => 0, // Half Day
            ];

            for ($day = 1; $day <= $daysInMonth; $day++) {
                $currentDate = Carbon::createFromDate($startDate->year, $startDate->month, $day);

                $att = $attendanceMap[$day] ?? null;
                $code = '-';
                $class = '';
                $style = 'text-align: center; padding: 2px; font-weight: bold; font-size: 11px;';

                if ($att) {
                    $type = $att->attendance_type;
                    if ($type === 'present') {
                        $code = 'P';
                        $class = 'present-cell';
                        $style .= ' color: green;';
                        $summary['P']++;
                    } elseif ($type === 'absent') {
                        $punchCount = isset($att->punch_count) ? $att->punch_count : 0;
                        if ($punchCount > 0) {
                            $code = 'MP';
                            $class = 'leave-half-cell';
                            $style .= ' color: orange;';
                            $summary['P'] += 0.5;
                            $summary['LWP'] += 0.5;
                        } else {
                            $code = 'LWP';
                            $class = 'absent-cell';
                            $style .= ' color: red;';
                            $summary['LWP']++;
                        }
                    } elseif ($type === 'half_day') {
                        $code = 'HD';
                        $class = 'leave-half-cell';
                        $style .= ' color: orange;';
                        $summary['P'] += 0.5;
                        $summary['LWP'] += 0.5;
                        $summary['HD']++;
                    } elseif ($type === 'leave') {
                        $shortName = $att->leave_type_short_name ?? 'L';
                        $code = $shortName;
                        $class = 'leave-cell';
                        $style .= ' color: blue;';

                        // Map leave short name
                        $shortNameUpper = strtoupper(trim($shortName));
                        $leaveKey = 'LWP';
                        if ($shortNameUpper === 'PL' || $shortNameUpper === 'PRIVILEGE LEAVE') {
                            $leaveKey = 'PL';
                        } elseif ($shortNameUpper === 'SL' || $shortNameUpper === 'SICK LEAVE') {
                            $leaveKey = 'SL';
                        } elseif ($shortNameUpper === 'DL' || $shortNameUpper === 'DUTY LEAVE') {
                            $leaveKey = 'DL';
                        } elseif (in_array($shortNameUpper, ['C-OFF', 'COFF', 'COMP-OFF', 'COMPOFF'])) {
                            $leaveKey = 'C-off';
                        } elseif ($shortNameUpper === 'LWP' || $shortNameUpper === 'LEAVE WITHOUT PAY') {
                            $leaveKey = 'LWP';
                        }

                        // Check if half day leave
                        if (isset($att->half_day) && $att->half_day) {
                            $code = $att->half_day === 'firsthalf' ? $shortName . '(FH)' : $shortName . '(SH)';
                            $summary[$leaveKey] += 0.5;
                            $summary['P'] += 0.5;
                        } else {
                            $summary[$leaveKey]++;
                        }
                    } elseif ($type === 'holiday') {
                        $code = 'H';
                        $class = 'holiday-cell';
                        $style .= ' color: purple;';
                        $summary['H']++;
                    } elseif ($type === 'week_off') {
                        $code = 'WO';
                        $class = 'week-off-cell';
                        $style .= ' color: gray;';
                        $summary['WO']++;
                    } elseif ($type === 'week_off_working') {
                        $code = 'WO(P)';
                        $class = 'present-cell';
                        $style .= ' color: green;';
                        $summary['P']++;
                    }
                } else {
                    if ($currentDate->gt($today)) {
                        $code = '-';
                    } else {
                        $code = 'LWP';
                        $class = 'absent-cell';
                        $style .= ' color: red;';
                        $summary['LWP']++;
                    }
                }

                $dayCells .= '<td class="' . $class . '" style="' . $style . '">' . $code . '</td>';
            }

            $scrollableBody .= '<tr>' . $dayCells . '</tr>';

            $empTotal = $summary['P'] + $summary['A'] + $summary['PL'] + $summary['SL'] + $summary['DL'] + $summary['C-off'] + $summary['LWP'] + $summary['H'] + $summary['WO'];

            // Right column (Summary)
            $rightBody .= '<tr>';
            $rightBody .= '<td class="text-center">' . $summary['P'] . '</td>';
            $rightBody .= '<td class="text-center">' . $summary['A'] . '</td>';
            $rightBody .= '<td class="text-center">' . $summary['PL'] . '</td>';
            $rightBody .= '<td class="text-center">' . $summary['SL'] . '</td>';
            $rightBody .= '<td class="text-center">' . $summary['DL'] . '</td>';
            $rightBody .= '<td class="text-center">' . $summary['C-off'] . '</td>';
            $rightBody .= '<td class="text-center">' . $summary['LWP'] . '</td>';
            $rightBody .= '<td class="text-center">' . $summary['H'] . '</td>';
            $rightBody .= '<td class="text-center">' . $summary['WO'] . '</td>';
            $rightBody .= '<td class="text-center" style="font-weight: bold;">' . $empTotal . '</td>';
            $rightBody .= '</tr>';

            // Accumulate grand totals
            $totalSum['P'] += $summary['P'];
            $totalSum['A'] += $summary['A'];
            $totalSum['PL'] += $summary['PL'];
            $totalSum['SL'] += $summary['SL'];
            $totalSum['DL'] += $summary['DL'];
            $totalSum['C-off'] += $summary['C-off'];
            $totalSum['LWP'] += $summary['LWP'];
            $totalSum['H'] += $summary['H'];
            $totalSum['WO'] += $summary['WO'];
            $totalSum['Total'] += $empTotal;
        }

        // Add Grand Total Rows
        if (count($processedEmployees) > 0) {
            $leftBody .= '<tr style="font-weight: bold; background-color: #f2f2f2;">';
            $leftBody .= '<td></td>';
            $leftBody .= '<td class="employee-col">Total Sum</td>';
            $leftBody .= '</tr>';

            $scrollableBody .= '<tr style="font-weight: bold; background-color: #f2f2f2;">';
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $scrollableBody .= '<td style="text-align: center; padding: 2px;"></td>';
            }
            $scrollableBody .= '</tr>';

            $rightBody .= '<tr style="font-weight: bold; background-color: #f2f2f2;">';
            $rightBody .= '<td class="text-center">' . $totalSum['P'] . '</td>';
            $rightBody .= '<td class="text-center">' . $totalSum['A'] . '</td>';
            $rightBody .= '<td class="text-center">' . $totalSum['PL'] . '</td>';
            $rightBody .= '<td class="text-center">' . $totalSum['SL'] . '</td>';
            $rightBody .= '<td class="text-center">' . $totalSum['DL'] . '</td>';
            $rightBody .= '<td class="text-center">' . $totalSum['C-off'] . '</td>';
            $rightBody .= '<td class="text-center">' . $totalSum['LWP'] . '</td>';
            $rightBody .= '<td class="text-center">' . $totalSum['H'] . '</td>';
            $rightBody .= '<td class="text-center">' . $totalSum['WO'] . '</td>';
            $rightBody .= '<td class="text-center">' . $totalSum['Total'] . '</td>';
            $rightBody .= '</tr>';
        }

        $rightHead = '<tr>';
        $rightHead .= '<th class="att-col-summary">P</th>';
        $rightHead .= '<th class="att-col-summary">A</th>';
        $rightHead .= '<th class="att-col-summary">PL</th>';
        $rightHead .= '<th class="att-col-summary">SL</th>';
        $rightHead .= '<th class="att-col-summary">DL</th>';
        $rightHead .= '<th class="att-col-summary">C-off</th>';
        $rightHead .= '<th class="att-col-summary">LWP</th>';
        $rightHead .= '<th class="att-col-summary">H</th>';
        $rightHead .= '<th class="att-col-summary">WO</th>';
        $rightHead .= '<th class="att-col-summary">Total</th>';
        $rightHead .= '</tr>';

        return [
            'daysHead' => $daysHead,
            'rightHead' => $rightHead,
            'leftBody' => $leftBody,
            'scrollableBody' => $scrollableBody,
            'rightBody' => $rightBody,
            'monthYear' => $monthYear
        ];
    }
    public function dayWiseDetails($employeeId, $date)
    {
        try {
            $employee = Employee::with(['employmentDetail', 'employmentDetail.designation', 'employmentDetail.department', 'branch'])->findOrFail($employeeId);
            $parsedDate = \Carbon\Carbon::parse($date);
            $monthStart = $parsedDate->copy()->startOfMonth();
            $monthEnd = $parsedDate->copy()->endOfMonth();

            // Fetch Holidays, Leaves, WeekOffs for the month (Reuse logic from getAttendanceReport/generateDetailedReport if possible)
            // Ideally should refactor data fetching into a service or shared private method.
            // For now, I'll fetch basically what I need.

            $companyId = $employee->company_id;

            // Fetch necessary maps (Leaves, Holidays, WeekOffs)
            // This is duplicative of getAttendanceReport but necessary for accurate stats.

            // 1. WeekOff Map
            $weekOffMap = [];
            // Correct logic using EmployeeWiseSalaryDetail
            $empSalaryDetail = \App\Models\EmployeeWiseSalaryDetail::where('company_id', $companyId)
                ->where('employee_id', $employeeId)
                ->first();

            $weekOffs = [];
            if ($empSalaryDetail && $empSalaryDetail->week_off) {
                $weekOffs = json_decode($empSalaryDetail->week_off, true) ?? [];
            }

            $dayMap = [
                'sun' => 0,
                'sunday' => 0,
                'mon' => 1,
                'monday' => 1,
                'tue' => 2,
                'tuesday' => 2,
                'wed' => 3,
                'wednesday' => 3,
                'thu' => 4,
                'thursday' => 4,
                'fri' => 5,
                'friday' => 5,
                'sat' => 6,
                'saturday' => 6
            ];

            $empWeekOffIndices = [];
            foreach ($weekOffs as $wo) {
                $woStr = strtolower(trim($wo));
                if (isset($dayMap[$woStr])) {
                    $empWeekOffIndices[] = $dayMap[$woStr];
                }
            }

            $weekOffMap[$employee->id] = $empWeekOffIndices;

            // 2. Holidays
            $holidaysList = \App\Models\Holiday::where('company_id', $companyId)
                ->where(function ($q) use ($monthStart, $monthEnd) {
                    $q->whereBetween('from_date', [$monthStart, $monthEnd])
                        ->orWhereBetween('to_date', [$monthStart, $monthEnd])
                        ->orWhere(function ($q2) use ($monthStart, $monthEnd) {
                            $q2->where('from_date', '<', $monthStart)
                                ->where('to_date', '>', $monthEnd);
                        });
                })->get();

            $holidays = [];
            foreach ($holidaysList as $h) {
                $start = \Carbon\Carbon::parse($h->from_date);
                $end = \Carbon\Carbon::parse($h->to_date);
                // Clamp to current month
                if ($start->lt($monthStart))
                    $start = $monthStart->copy();
                if ($end->gt($monthEnd))
                    $end = $monthEnd->copy();

                for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
                    $holidays[$d->format('Y-m-d')] = $d->format('Y-m-d'); // Format as expected by processor
                }
            }

            // 3. Leaves
            $leaves = \App\Models\LeaveApplication::where('employee_id', $employeeId)
                ->where('status', 'Approved')
                ->where(function ($q) use ($monthStart, $monthEnd) {
                    $q->whereBetween('fromdate_time', [$monthStart, $monthEnd])
                        ->orWhereBetween('todate_time', [$monthStart, $monthEnd]);
                })
                ->get();

            $leaveDates = [];
            foreach ($leaves as $leave) {
                // Handle date/datetime parsing
                $from = \Carbon\Carbon::parse($leave->fromdate_time);
                $to = \Carbon\Carbon::parse($leave->todate_time ?? $leave->fromdate_time);

                $period = \Carbon\CarbonPeriod::create($from, $to);
                foreach ($period as $dt) {
                    if ($dt->between($monthStart, $monthEnd)) {
                        $leaveDates[$employee->id][$dt->format('Y-m-d')][] = [
                            'type' => $leave->leave_type_id,
                            'reason' => $leave->leave_reason,
                            'halfday_fullday' => $leave->halfday_fullday,
                            'firsthalf_secondhalf' => $leave->firsthalf_secondhalf,
                            'payment_mode' => 1, // Assume paid? Need logic.
                        ];
                    }
                }
            }

            // Calculate Monthly Stats
            $processedData = $this->processDetailedEmployeeData($employee, $monthStart, $monthEnd, $weekOffMap, $holidays, $leaveDates);

            // Get specific day details
            $dayData = $processedData['attendances']->firstWhere('attendance_date', $date);

            // Get ALL raw punches for the day for the table
            $rawPunches = Attendance::where('employee_id', $employeeId)
                ->whereDate('attendance_date', $date)
                ->orderBy('punch_in_time')
                ->get();

            return view('software.modules.reports.attendance-report.day_wise_details', [
                'employee' => $employee,
                'date' => $parsedDate,
                'dayData' => $dayData,
                'monthlyStats' => $processedData,
                'rawPunches' => $rawPunches
            ]);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
