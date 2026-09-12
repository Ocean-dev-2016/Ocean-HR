<?php

namespace App\Http\Controllers\software;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Company;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\Rule;
use App\Exports\PunchInRecordExport;
use Maatwebsite\Excel\Facades\Excel;

class PunchInRecordReportController extends Controller
{
    public $modules = [];

    public function __construct()
    {
        parent::__construct();

        $this->modules = [
            'title' => 'Punch-In Record Report',
            'folder_path' => 'software.modules.reports.punch-in-record-report',
            'route' => 'punch-in-record-report',
            'permisstion_prefix' => 'attendance-report',
            'module_name' => 'Attendance Report',
            'company_id' => ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails : null,
        ];
    }

    public function index(Request $request)
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

        return view($modules['folder_path'] . '.index');
    }

    public function getReport(Request $request)
    {
        $data = $this->fetchReportData($request);

        if (isset($data['error'])) {
            return response()->json(['status' => 'error', 'message' => $data['message']]);
        }

        $html = View::make($this->modules['folder_path'] . '.report-table', ['data' => $data])->render();

        return response()->json([
            'status' => 'success',
            'html' => $html
        ]);
    }

    public function print(Request $request)
    {
        $data = $this->fetchReportData($request);
        if (isset($data['error'])) {
            return redirect()->back()->withErrors($data['message']);
        }

        $company = Company::find($request->company_id);
        $employee = $request->employee_id ? Employee::find($request->employee_id) : null;
        $monthName = Carbon::create()->month((int) $request->month)->format('F');
        $period = $monthName . ' ' . $request->year;

        return view($this->modules['folder_path'] . '.print', [
            'data' => $data,
            'company' => $company,
            'employee' => $employee,
            'period' => $period
        ]);
    }

    public function exportExcel(Request $request)
    {
        $data = $this->fetchReportData($request);
        if (isset($data['error'])) {
            return redirect()->back()->withErrors($data['message']);
        }
        return Excel::download(new PunchInRecordExport($data, $this->modules), 'Punch-In-Record-Report-' . date('Y-m-d-H-i-s') . '.xlsx');
    }

    private function fetchReportData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => ['required', Rule::exists((new Company())->getTable(), 'id')],
            'employee_id' => ['nullable', Rule::exists((new Employee())->getTable(), 'id')],
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer'],
        ]);

        if ($validator->fails()) {
            return ['error' => true, 'message' => $validator->errors()->first()];
        }

        try {
            $companyId = $request->company_id;
            $month = $request->month;
            $year = $request->year;

            $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();

            // Fetch Raw Attendance Records for the period
            $query = Attendance::with(['employee', 'shift', 'employee.employmentDetail.department'])
                ->where('company_id', $companyId)
                ->whereBetween('attendance_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);

            if ($request->employee_id) {
                $query->where('employee_id', $request->employee_id);
            }

            if ($request->department_id) {
                $deptId = $request->department_id;
                $query->whereHas('employee', function ($q) use ($deptId) {
                    $q->whereHas('employmentDetail', function ($q2) use ($deptId) {
                        $q2->where('department_id', $deptId);
                    });
                });
            }

            $rawAttendances = $query->orderBy('employee_id')
                ->orderBy('attendance_date', 'ASC')
                ->orderBy('punch_in_time', 'ASC')
                ->get();

            // Group by Employee and Date
            $grouped = $rawAttendances->groupBy(function ($item) {
                return $item->employee_id . '_' . $item->attendance_date;
            });

            $reportData = [];

            foreach ($grouped as $key => $records) {
                $firstRecord = $records->first();
                $employee = $firstRecord->employee;
                $dateStr = $firstRecord->attendance_date;
                $shift = $firstRecord->shift;

                if (!$employee) {
                    continue;
                }

                // In Time & Out Time extraction
                $inRecords = $records->filter(fn($r) => strtolower($r->attendace_type) === 'in')->sortBy('punch_in_time');
                $outRecords = $records->filter(fn($r) => strtolower($r->attendace_type) === 'out')->sortByDesc('punch_in_time');

                $inRecord = $inRecords->first();
                $outRecord = $outRecords->first();

                $timeIn = $inRecord ? $this->extractTime($inRecord->punch_in_time) : null;
                $timeOut = $outRecord ? $this->extractTime($outRecord->punch_in_time) : null;

                // 1. Calculate Worked Minutes
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
                        $inTimeObj = new \DateTime($allPunches[$i]['time']);
                        $outTimeObj = new \DateTime($allPunches[$i + 1]['time']);
                        $diff = $outTimeObj->diff($inTimeObj);
                        $minutes = ($diff->h * 60) + $diff->i + floor($diff->s / 60);
                        $totalPunchMinutes += $minutes;
                        $i++;
                    }
                }

                $workedMinutes = $totalPunchMinutes;

                // Calculate Actual Break Minutes from punches
                $actualBreakMinutes = 0;
                for ($k = 0; $k < count($allPunches) - 1; $k++) {
                    if (
                        strtolower($allPunches[$k]['type']) === 'out' &&
                        strtolower($allPunches[$k + 1]['type']) === 'in'
                    ) {
                        $outTimeObj = new \DateTime($allPunches[$k]['time']);
                        $inTimeObj = new \DateTime($allPunches[$k + 1]['time']);
                        $diff = $inTimeObj->diff($outTimeObj);
                        $minutes = ($diff->h * 60) + $diff->i + floor($diff->s / 60);
                        $actualBreakMinutes += $minutes;
                    }
                }

                // Break Time Determination
                if ($actualBreakMinutes > 0) {
                    // Show actual break time from punches
                    $breakHours = floor($actualBreakMinutes / 60);
                    $breakMins = $actualBreakMinutes % 60;
                    $breakTimeStr = sprintf('%02d:%02d', $breakHours, $breakMins);
                    // workedMinutes already excludes this break because it is sum of IN->OUT segments.
                } else {
                    // If no actual punch break, check shift breaking hour
                    $breakTimeStr = '00:00';
                    if ($shift && $shift->breaking_hour) {
                        $breakMinutes = $this->timeToMinutes($shift->breaking_hour);
                        if ($breakMinutes > 0) {
                            $workedMinutes = max(0, $workedMinutes - $breakMinutes);
                            $breakTimeStr = Carbon::parse($shift->breaking_hour)->format('H:i');
                        }
                    }
                }

                // Total Working Hours formatted
                $hours = floor($workedMinutes / 60);
                $minutes = $workedMinutes % 60;
                $workingHours = sprintf('%02d:%02d', $hours, $minutes);

                // Late By calculation
                $lateByStr = '-';
                $isLate = false;
                if ($shift && $timeIn && $shift->punch_in_minimum) {
                    $shiftStartMinutes = $this->timeToMinutes($shift->punch_in_minimum);
                    $grace = $shift->in_out_grace_period ?? $shift->grace_period ?? 0;
                    $shiftStartMinutesWithGrace = $shiftStartMinutes + (int) $grace;
                    $inMinutes = $this->timeToMinutes($timeIn);

                    if ($inMinutes > $shiftStartMinutesWithGrace) {
                        $isLate = true;
                        $lateMins = $inMinutes - $shiftStartMinutes;
                        $lateByStr = sprintf('%02d:%02d', floor($lateMins / 60), $lateMins % 60);
                    }
                }

                // Early Going calculation
                $earlyGoingStr = '-';
                $isEarly = false;
                if ($shift && $timeOut && $shift->punch_out) {
                    $shiftEndMinutes = $this->timeToMinutes($shift->punch_out);
                    $outMinutes = $this->timeToMinutes($timeOut);

                    if ($outMinutes < $shiftEndMinutes) {
                        $isEarly = true;
                        $earlyMins = $shiftEndMinutes - $outMinutes;
                        $earlyGoingStr = sprintf('%02d:%02d', floor($earlyMins / 60), $earlyMins % 60);
                    }
                }

                // OT Hours calculation
                $otStr = '-';
                if ($shift && $timeOut && $shift->punch_out) {
                    $shiftEndMinutes = $this->timeToMinutes($shift->punch_out);
                    $outMinutes = $this->timeToMinutes($timeOut);

                    if ($outMinutes > $shiftEndMinutes) {
                        $otMins = $outMinutes - $shiftEndMinutes;
                        $otStr = sprintf('%02d:%02d', floor($otMins / 60), $otMins % 60);
                    }
                }

                // Status determination
                $status = 'Present';
                if ($isLate && $isEarly) {
                    $status = 'Late & Early Going';
                } elseif ($isLate) {
                    $status = 'Late';
                } elseif ($isEarly) {
                    $status = 'Early Going';
                }

                // Remarks
                $remarks = $firstRecord->remark ?? '-';

                // Extract all unique IN and OUT punch times in chronological order
                $inTimes = $inRecords->map(function ($r) {
                    $time = $this->extractTime($r->punch_in_time);
                    return $time ? Carbon::parse($time)->format('h:i A') : null;
                })->filter()->unique()->values()->toArray();

                $outTimes = $outRecords->sortBy('punch_in_time')->map(function ($r) {
                    $time = $this->extractTime($r->punch_in_time);
                    return $time ? Carbon::parse($time)->format('h:i A') : null;
                })->filter()->unique()->values()->toArray();

                $reportData[] = [
                    'date' => Carbon::parse($dateStr)->format('d-m-Y'),
                    'employee_code' => $employee->employee_code ?? '-',
                    'employee_name' => $employee->full_name ?? '-',
                    'department' => $employee->employmentDetail->department->name ?? '-',
                    'in_times' => empty($inTimes) ? ['-'] : $inTimes,
                    'out_times' => empty($outTimes) ? ['-'] : $outTimes,
                    'in_time' => empty($inTimes) ? '-' : implode(', ', $inTimes),
                    'out_time' => empty($outTimes) ? '-' : implode(', ', $outTimes),
                    'break_time' => $breakTimeStr,
                    'total_working_hours' => $workingHours,
                    'late_by' => $lateByStr,
                    'early_going' => $earlyGoingStr,
                    'ot_hours' => $otStr,
                    'status' => $status,
                    'remarks' => $remarks
                ];
            }

            // Sort by Date and then Employee Name
            usort($reportData, function ($a, $b) {
                $dateA = strtotime($a['date']);
                $dateB = strtotime($b['date']);
                if ($dateA == $dateB) {
                    return strcmp($a['employee_name'], $b['employee_name']);
                }
                return $dateA - $dateB;
            });

            return $reportData;

        } catch (\Exception $e) {
            return ['error' => true, 'message' => $e->getMessage()];
        }
    }

    private function extractTime($timeStr)
    {
        if (!$timeStr)
            return null;
        if (strpos($timeStr, ' ') !== false) {
            return explode(' ', $timeStr)[1] ?? null;
        }
        return $timeStr;
    }

    private function timeToMinutes($timeStr)
    {
        if (!$timeStr)
            return 0;
        $parts = explode(':', $timeStr);
        return ((int) ($parts[0] ?? 0) * 60) + (int) ($parts[1] ?? 0);
    }
}
