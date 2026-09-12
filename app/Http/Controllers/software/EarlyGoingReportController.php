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
use App\Exports\EarlyGoingExport;
use Maatwebsite\Excel\Facades\Excel;

class EarlyGoingReportController extends Controller
{
    public $modules = [];

    public function __construct()
    {
        parent::__construct();

        $this->modules = [
            'title' => 'Early Going Report',
            'folder_path' => 'software.modules.reports.early-going-report',
            'route' => 'early-going-report',
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
        return Excel::download(new EarlyGoingExport($data, $this->modules), 'Early-Going-Report-' . date('Y-m-d-H-i-s') . '.xlsx');
    }

    private function fetchReportData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => ['required', Rule::exists((new Company())->getTable(), 'id')],
            'employee_id' => ['nullable', Rule::exists((new Employee())->getTable(), 'id')],
            'department_id' => ['nullable', Rule::exists((new \App\Models\Department())->getTable(), 'id')],
            'date' => ['nullable', 'date'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'month' => ['nullable', 'integer', 'between:1,12'],
            'year' => ['nullable', 'integer'],
        ]);

        if ($validator->fails()) {
            return ['error' => true, 'message' => $validator->errors()->first()];
        }

        try {
            $companyId = $request->company_id;

            if ($request->filled('start_date') || $request->filled('end_date')) {
                $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : Carbon::parse($request->end_date)->startOfDay();
                $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : Carbon::parse($request->start_date)->endOfDay();
            } elseif ($request->filled('date')) {
                $startDate = Carbon::parse($request->date)->startOfDay();
                $endDate = $startDate->copy()->endOfDay();
            } else {
                $month = $request->month ?: date('n');
                $year = $request->year ?: date('Y');
                $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
                $endDate = $startDate->copy()->endOfMonth();
            }

            // Fetch Raw Punches (Only Out Punches)
            $query = Attendance::with(['employee', 'shift', 'employee.employmentDetail.department'])
                ->where('company_id', $companyId)
                ->where('attendace_type', 'out')
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
                ->orderBy('punch_in_time', 'DESC') // Latest out punch of the day
                ->get();

            // Also fetch first 'in' punches for the same date range to compare punch-in status
            $attendanceInRecordsForEarlyGo = Attendance::where('company_id', $companyId)
                ->where('attendace_type', 'in')
                ->whereBetween('attendance_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->orderBy('attendance_date', 'ASC')
                ->orderBy('punch_in_time', 'ASC')
                ->get()
                ->groupBy(function ($item) {
                    return $item->employee_id . '_' . $item->attendance_date;
                });

            // Group by Employee and Date for 'out' punches
            $grouped = $rawAttendances->groupBy(function ($item) {
                return $item->employee_id . '_' . $item->attendance_date;
            });

            $earlyGoingData = [];

            foreach ($grouped as $key => $dayRecords) {
                $lastRecord = $dayRecords->first(); // Since we ordered by punch_in_time DESC, first is latest out
                $employee = $lastRecord->employee;
                $dateStr = $lastRecord->attendance_date;
                $shift = $lastRecord->shift;

                if ($shift && $shift->punch_out) {
                    // Construct shift end time
                    $shiftEnd = Carbon::parse($dateStr . ' ' . $shift->punch_out);

                    // Use in_out_grace_period when calculating early go threshold
                    $graceMinutes = (int) ($shift->in_out_grace_period ?? 0);
                    $shiftEnd->subMinutes($graceMinutes);

                    // Out Time
                    $outTimeStr = $lastRecord->punch_in_time; // punch_in_time field is used for punch time in this table
                    if (strpos($outTimeStr, ' ') !== false) {
                        $parts = explode(' ', $outTimeStr);
                        $outTimeStr = $parts[1];
                    }
                    $outTime = Carbon::parse($dateStr . ' ' . $outTimeStr);

                    // Determine if the employee punched in on time
                    $punchInOnTime = false;
                    $inKey = $lastRecord->employee_id . '_' . $dateStr;
                    $firstInPunch = $attendanceInRecordsForEarlyGo[$inKey]->first() ?? null;
                    if ($firstInPunch && $shift->punch_in_minimum) {
                        $firstPunchInTimeStr = $firstInPunch->punch_in_time;
                        if (strpos($firstPunchInTimeStr, ' ') !== false) {
                            $parts = explode(' ', $firstPunchInTimeStr);
                            $firstPunchInTimeStr = $parts[1];
                        }

                        $shiftStart = Carbon::parse($dateStr . ' ' . $shift->punch_in_minimum);
                        $shiftStart->addMinutes($graceMinutes);
                        $firstPunchInTime = Carbon::parse($dateStr . ' ' . $firstPunchInTimeStr);

                        $punchInOnTime = $firstPunchInTime->lte($shiftStart);
                    }

                    // Early Going Check: outTime is before adjusted shiftEnd AND employee did not punch in on time
                    if ($outTime->lessThan($shiftEnd) && !$punchInOnTime) {
                        $earlyGoingData[] = [
                            'date' => Carbon::parse($dateStr)->format('d-m-Y'),
                            'employee_code' => $employee->employee_code ?? '-',
                            'employee_name' => $employee->full_name ?? '-',
                            'department' => $employee->employmentDetail->department->name ?? '-',
                            'out_time' => $outTime->format('H:i:s'),
                            'expected_out_time' => $shiftEnd->format('H:i:s'),
                            'early_by' => abs($shiftEnd->diffInMinutes($outTime, false)) . ' mins',
                            'shift_name' => $shift->name ?? '-',
                            'shift_time' => Carbon::parse($shift->punch_in_minimum)->format('H:i') . ' - ' . ($shift->punch_out ? Carbon::parse($shift->punch_out)->format('H:i') : '-'),
                        ];
                    }
                }
            }

            // Sort by Employee Name and then Date
            usort($earlyGoingData, function ($a, $b) {
                if ($a['employee_name'] == $b['employee_name']) {
                    return strtotime($a['date']) - strtotime($b['date']);
                }
                return strcmp($a['employee_name'], $b['employee_name']);
            });

            return $earlyGoingData;

        } catch (\Exception $e) {
            return ['error' => true, 'message' => $e->getMessage()];
        }
    }
}
