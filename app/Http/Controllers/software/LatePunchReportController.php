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
use App\Exports\LatePunchExport;
use Maatwebsite\Excel\Facades\Excel;

class LatePunchReportController extends Controller
{
    public $modules = [];

    public function __construct()
    {
        parent::__construct();

        $this->modules = [
            'title' => 'Late Punch Report',
            'folder_path' => 'software.modules.reports.late-punch-report',
            'route' => 'late-punch-report',
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
        return Excel::download(new LatePunchExport($data, $this->modules), 'Late-Punch-Report-' . date('Y-m-d-H-i-s') . '.xlsx');
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

            // Fetch Raw Punches (Only In Punches)
            $query = Attendance::with(['employee', 'shift', 'employee.employmentDetail.department'])
                ->where('company_id', $companyId)
                ->where('attendace_type', 'in')
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

            $latePunchData = [];

            foreach ($grouped as $key => $dayRecords) {
                $firstRecord = $dayRecords->first();
                $employee = $firstRecord->employee;
                $dateStr = $firstRecord->attendance_date;
                $shift = $firstRecord->shift;

                if ($shift && $shift->punch_in_minimum && ($shift->in_out_grace_period !== null || $shift->grace_period !== null)) {
                    // Construct shift start time on that specific day
                    $shiftStart = Carbon::parse($dateStr . ' ' . $shift->punch_in_minimum);

                    // Ensure inTime is on the same date as shiftStart for accurate comparison
                    $inTimeStr = $firstRecord->punch_in_time;
                    if (strpos($inTimeStr, ' ') !== false) {
                        $parts = explode(' ', $inTimeStr);
                        $inTimeStr = $parts[1];
                    }
                    $inTime = Carbon::parse($dateStr . ' ' . $inTimeStr);

                    // Use in_out_grace_period if available, otherwise fallback to grace_period
                    $graceMinutes = (int) ($shift->in_out_grace_period ?? $shift->grace_period ?? 0);
                    $graceLimit = $shiftStart->copy()->addMinutes($graceMinutes);

                    // If punch in time is greater than grace limit, it's late
                    if ($inTime->greaterThan($graceLimit)) {
                        $latePunchData[] = [
                            'date' => Carbon::parse($dateStr)->format('d-m-Y'),
                            'employee_code' => $employee->employee_code ?? '-',
                            'employee_name' => $employee->full_name ?? '-',
                            'department' => $employee->employmentDetail->department->name ?? '-',
                            'in_time' => $inTime->format('H:i:s'),
                            'expected_time' => $shiftStart->format('H:i:s'),
                            'late_by' => abs($inTime->diffInMinutes($shiftStart, false)) . ' mins',
                            'shift_name' => $shift->name ?? '-',
                            'shift_time' => Carbon::parse($shift->punch_in_minimum)->format('H:i') . ' - ' . ($shift->punch_out ? Carbon::parse($shift->punch_out)->format('H:i') : '-'),
                        ];
                    }
                }
            }

            // Sort by Employee Name and then Date
            usort($latePunchData, function ($a, $b) {
                if ($a['employee_name'] == $b['employee_name']) {
                    return strtotime($a['date']) - strtotime($b['date']);
                }
                return strcmp($a['employee_name'], $b['employee_name']);
            });

            return $latePunchData;

        } catch (\Exception $e) {
            return ['error' => true, 'message' => $e->getMessage()];
        }
    }
}
