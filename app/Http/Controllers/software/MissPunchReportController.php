<?php

namespace App\Http\Controllers\software;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\Rule;
use App\Exports\MissPunchExport;
use Maatwebsite\Excel\Facades\Excel;

class MissPunchReportController extends Controller
{
    public $modules = [];

    public function __construct()
    {
        parent::__construct();

        $this->modules = [
            'title' => 'Miss Punch Report',
            'folder_path' => 'software.modules.reports.miss-punch-report',
            'route' => 'miss-punch-report',
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
        return view($this->modules['folder_path'] . '.print', ['data' => $data]);
    }

    public function exportExcel(Request $request)
    {
        $data = $this->fetchReportData($request);
        if (isset($data['error'])) {
            return redirect()->back()->withErrors($data['message']);
        }
        return Excel::download(new MissPunchExport($data, $this->modules), 'Miss-Punch-Report-' . date('Y-m-d-H-i-s') . '.xlsx');
    }

    private function fetchReportData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => ['required', Rule::exists((new Company())->getTable(), 'id')],
            'employee_id' => ['nullable', Rule::exists((new Employee())->getTable(), 'id')],
            'department_id' => ['nullable', Rule::exists((new \App\Models\Department())->getTable(), 'id')],
            'date' => ['nullable', 'date'],
            'month' => ['nullable', 'integer', 'between:1,12'],
            'year' => ['nullable', 'integer'],
        ]);

        if ($validator->fails()) {
            return ['error' => true, 'message' => $validator->errors()->first()];
        }

        try {
            $companyId = $request->company_id;

            if ($request->filled('date')) {
                $startDate = Carbon::parse($request->date);
                $endDate = $startDate->copy();
            } else {
                $month = $request->month ?: date('n');
                $year = $request->year ?: date('Y');
                $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
                $endDate = $startDate->copy()->endOfMonth();
            }

            // Fetch Raw Punches
            $query = Attendance::with(['employee', 'shift', 'employee.employmentDetail.department', 'employee.employmentDetail.designation'])
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

            $rawAttendances = $query->orderBy('attendance_date', 'ASC')
                ->orderBy('punch_in_time', 'ASC')
                ->get();

            // Group by Employee and Date to aggregate daily punches
            $grouped = $rawAttendances->groupBy(function ($item) {
                return $item->employee_id . '_' . $item->attendance_date;
            });

            $missPunchData = [];

            foreach ($grouped as $key => $dayRecords) {
                $firstRecord = $dayRecords->first();
                $employee = $firstRecord->employee;
                $dateStr = $firstRecord->attendance_date;

                // Aggregate Punches
                $punches = $dayRecords->map(function ($r) {
                    return [
                        'type' => strtolower($r->attendace_type),
                        'time' => $r->punch_in_time
                    ];
                })->values()->toArray();

                // Calculate Duration
                $totalMinutes = 0;
                $count = count($punches);

                for ($i = 0; $i < $count; $i++) {
                    if (
                        isset($punches[$i]['type']) && $punches[$i]['type'] == 'in' &&
                        isset($punches[$i + 1]['type']) && $punches[$i + 1]['type'] == 'out'
                    ) {
                        try {
                            $in = Carbon::parse($punches[$i]['time']);
                            $out = Carbon::parse($punches[$i + 1]['time']);
                            $totalMinutes += $out->diffInMinutes($in);
                        } catch (\Exception $e) {
                        }
                        $i++;
                    }
                }

                // Break Deduction
                $shift = $firstRecord->shift;
                $halfDayLimit = 240;
                if ($shift) {
                    if ($shift->breaking_hour) {
                        $break = $this->timeToMinutes($shift->breaking_hour);
                        $totalMinutes = max(0, $totalMinutes - $break);
                    }
                    if ($shift->half_day_hour) {
                        $halfDayLimit = $this->timeToMinutes($shift->half_day_hour);
                    }
                }

                $hours = floor($totalMinutes / 60);
                $mins = $totalMinutes % 60;
                $workingDuration = sprintf('%02d:%02d', $hours, $mins);

                // Miss Punch Logic
                $punchCount = count($punches);
                $isOddPunches = ($punchCount > 0 && $punchCount % 2 != 0);

                $isToday = (Carbon::now()->format('Y-m-d') === $dateStr);
                if ($isToday && $isOddPunches) {
                    $isOddPunches = false;
                }

                $isShortDuration = ($punchCount > 0 && $totalMinutes > 0 && $totalMinutes < $halfDayLimit);

                $isMarkedHalfDay = $dayRecords->contains(fn($r) => in_array(strtolower($r->attendace_type), ['half_day', 'hd']));
                $isMarkedLeave = $dayRecords->contains(fn($r) => strtolower($r->attendace_type) === 'leave');

                if ($isMarkedHalfDay || $isMarkedLeave) {
                    $isShortDuration = false;
                }

                if ($isOddPunches || $isShortDuration) {
                    $reason = [];
                    if ($isOddPunches)
                        $reason[] = 'Odd Punches (' . $punchCount . ')';
                    if ($isShortDuration)
                        $reason[] = 'Short Duration (' . $workingDuration . ')';

                    $inTime = isset($punches[0]) ? Carbon::parse($punches[0]['time'])->format('H:i') : '-';
                    $outTime = isset($punches[$count - 1]) ? Carbon::parse($punches[$count - 1]['time'])->format('H:i') : '-';
                    if ($isOddPunches && $punchCount == 1)
                        $outTime = '-';

                    $missPunchData[] = [
                        'date' => Carbon::parse($dateStr)->format('d-m-Y'),
                        'employee_code' => $employee->employee_code ?? '-',
                        'employee_name' => $employee->full_name ?? '-',
                        'department' => $employee->employmentDetail->department->name ?? '-',
                        'in_time' => $inTime,
                        'out_time' => $outTime,
                        'shift_name' => $shift->name ?? '-',
                        'shift_time' => ($shift && $shift->punch_in_minimum && $shift->punch_out) ?
                            Carbon::parse($shift->punch_in_minimum)->format('H:i') . ' - ' . Carbon::parse($shift->punch_out)->format('H:i') : '-',
                        'status' => 'Miss Punch',
                        'reason' => implode(', ', $reason),
                        'punches' => $punches
                    ];
                }
            }

            return $missPunchData;

        } catch (\Exception $e) {
            return ['error' => true, 'message' => $e->getMessage()];
        }
    }

    private function timeToMinutes($timeString)
    {
        if (empty($timeString))
            return 0;
        $parts = explode(':', $timeString);
        return ($parts[0] * 60) + ($parts[1] ?? 0);
    }
}
