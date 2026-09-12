<?php

namespace App\Http\Controllers\software;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmploymentDetail;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DailyAttendanceReportExport;

class DailyAttendanceReportController extends Controller
{
    public $modules = [];

    public function __construct()
    {
        parent::__construct();

        $this->modules = [
            'title' => 'Daily Attendance Report',
            'folder_path' => 'software.modules.reports.daily-attendance-report',
            'route' => 'daily-attendance-report',
            'permisstion_prefix' => 'attendance-report',
            'module_name' => 'Attendance Report',
            'company_id' => ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails : null,
        ];
    }

    /**
     * Display the filter form.
     */
    public function index(Request $request)
    {
        $modules = $this->modules;
        $modules['authLoginUserDetail'] = $this->authenticateLoginUserDetails ?? null;
        $modules['company_id'] = $this->authenticateLoginUserDetails?->company_id ?? null;
        $modules['parent_type_id'] = $this->authenticateLoginUserDetails?->parent_type_id ?? null;

        if (count(config('constants.permissions'))) {
            foreach (config('constants.permissions') as $key => $value) {
                $modules[$value . '_permission'] = (isset($modules['company_id']) && !$modules['company_id'])
                    ? true
                    : Gate::check('hasPermission', [$value, $modules['module_name']]);
            }
        }

        if (!$modules['view_permission']) {
            if ($request->ajax()) {
                return $this->sendError('Unauthorized', [], [], 403);
            }
            abort(403, 'Unauthorized');
        }

        View::share('modules', $modules);

        return view($modules['folder_path'] . '.index');
    }

    /**
     * AJAX: return rendered report-table HTML.
     */
    public function getReport(Request $request)
    {
        $result = $this->fetchReportData($request);

        if (isset($result['error'])) {
            return response()->json(['status' => 'error', 'message' => $result['message']]);
        }

        $html = View::make(
            $this->modules['folder_path'] . '.report-table',
            ['data' => $result['data'], 'summary' => $result['summary'], 'date' => $result['date']]
        )->render();

        return response()->json(['status' => 'success', 'html' => $html]);
    }

    /**
     * Print view (standalone, no layout).
     */
    public function print(Request $request)
    {
        $result = $this->fetchReportData($request);

        if (isset($result['error'])) {
            return redirect()->back()->withErrors($result['message']);
        }

        $company = Company::find($request->company_id);

        return view($this->modules['folder_path'] . '.print', [
            'data' => $result['data'],
            'summary' => $result['summary'],
            'date' => $result['date'],
            'company' => $company,
        ]);
    }

    /**
     * Export to Excel.
     */
    public function exportExcel(Request $request)
    {
        $result = $this->fetchReportData($request);

        if (isset($result['error'])) {
            return redirect()->back()->withErrors($result['message']);
        }

        return Excel::download(
            new DailyAttendanceReportExport($result['data'], $result['summary'], $result['date'], $this->modules),
            'Daily-Attendance-Report-' . date('Y-m-d-H-i-s') . '.xlsx'
        );
    }

    /**
     * Core data-fetching logic shared by all actions.
     */
    private function fetchReportData(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'company_id' => ['required', Rule::exists((new Company())->getTable(), 'id')],
            'date' => ['required', 'date'],
            'department_id' => ['nullable', Rule::exists((new Department())->getTable(), 'id')],
            'shift_id' => ['nullable', Rule::exists((new Shift())->getTable(), 'id')],
            'employee_id' => ['nullable', Rule::exists((new Employee())->getTable(), 'id')],
            'status' => ['nullable', 'in:present,absent,all'],
        ]);

        if ($validator->fails()) {
            return ['error' => true, 'message' => $validator->errors()->first()];
        }

        try {
            $companyId = $request->company_id;
            $date = Carbon::parse($request->date);
            $statusFilter = $request->status ?? 'all';

            // -------------------------------------------------------
            // 1. Fetch all active employees for this company/filters
            // -------------------------------------------------------
            $employeeQuery = Employee::with([
                'employmentDetail',
                'employmentDetail.department',
                'employmentDetail.designation',
                'employmentDetail.shiftDetail',
            ])
                ->where('company_id', $companyId)
                ->where('status', 'active');

            if ($request->filled('employee_id')) {
                $employeeQuery->where('id', $request->employee_id);
            }

            if ($request->filled('department_id')) {
                $deptId = $request->department_id;
                $employeeQuery->whereHas('employmentDetail', function ($q) use ($deptId) {
                    $q->where('department_id', $deptId);
                });
            }

            if ($request->filled('shift_id')) {
                $shiftId = $request->shift_id;
                $employeeQuery->whereHas('employmentDetail', function ($q) use ($shiftId) {
                    $q->where('shift', $shiftId);
                });
            }

            $employees = $employeeQuery->orderBy('full_name')->get();

            // -------------------------------------------------------
            // 2. Fetch attendance records for that date
            // -------------------------------------------------------
            $attendanceQuery = Attendance::where('company_id', $companyId)
                ->where('attendance_date', $date->format('Y-m-d'));

            if ($request->filled('employee_id')) {
                $attendanceQuery->where('employee_id', $request->employee_id);
            }

            $attendances = $attendanceQuery->get()->groupBy('employee_id');

            // -------------------------------------------------------
            // 3. Build report rows
            // -------------------------------------------------------
            $reportData = [];
            $presentCount = 0;
            $absentCount = 0;

            foreach ($employees as $employee) {
                $empAttendances = $attendances->get($employee->id, collect());
                $isPresent = $empAttendances->isNotEmpty();

                $rowStatus = $isPresent ? 'Present' : 'Absent';

                // Apply status filter
                if ($statusFilter === 'present' && !$isPresent) {
                    continue;
                }
                if ($statusFilter === 'absent' && $isPresent) {
                    continue;
                }

                // Punch times
                $inRecords = $empAttendances->filter(fn($r) => strtolower($r->attendace_type) === 'in')->sortBy('punch_in_time');
                $outRecords = $empAttendances->filter(fn($r) => strtolower($r->attendace_type) === 'out')->sortByDesc('punch_in_time');

                $firstIn = $inRecords->first();
                $lastOut = $outRecords->first();

                $inTime = $firstIn ? $this->extractTime($firstIn->punch_in_time) : null;
                $outTime = $lastOut ? $this->extractTime($lastOut->punch_in_time) : null;

                $inTimeFormatted = $inTime ? Carbon::parse($inTime)->format('h:i A') : '-';
                $outTimeFormatted = $outTime ? Carbon::parse($outTime)->format('h:i A') : '-';

                // Working hours
                $workingHours = '-';
                if ($inTime && $outTime) {
                    $inMinutes = $this->timeToMinutes($inTime);
                    $outMinutes = $this->timeToMinutes($outTime);
                    if ($outMinutes > $inMinutes) {
                        $diff = $outMinutes - $inMinutes;
                        $workingHours = sprintf('%02d:%02d', floor($diff / 60), $diff % 60);
                    }
                }

                // Shift info from employment detail
                $empDetail = $employee->employmentDetail;
                $shift = $empDetail?->shiftDetail ?? null;
                $shiftName = $shift?->name ?? '-';
                $designation = $empDetail?->designation?->name ?? '-';
                $department = $empDetail?->department?->name ?? '-';

                if ($isPresent) {
                    $presentCount++;
                } else {
                    $absentCount++;
                }

                $reportData[] = [
                    'employee_id' => $employee->id,
                    'employee_code' => $employee->employee_code ?? '-',
                    'employee_name' => $employee->full_name ?? '-',
                    'shift_name' => $shiftName,
                    'designation' => $designation,
                    'department' => $department,
                    'date' => $date->format('d-m-Y'),
                    'status' => $rowStatus,
                    'in_time' => $inTimeFormatted,
                    'out_time' => $outTimeFormatted,
                    'working_hours' => $workingHours,
                    'punch_count' => $empAttendances->count(),
                ];
            }

            $summary = [
                'total' => count($reportData),
                'present' => $presentCount,
                'absent' => $absentCount,
                'date' => $date->format('d M Y'),
            ];

            return [
                'data' => $reportData,
                'summary' => $summary,
                'date' => $date->format('d-m-Y'),
            ];

        } catch (\Exception $e) {
            return ['error' => true, 'message' => $e->getMessage()];
        }
    }

    // -------------------------------------------------------
    // Helpers
    // -------------------------------------------------------

    private function extractTime(?string $timeStr): ?string
    {
        if (!$timeStr) {
            return null;
        }
        if (strpos($timeStr, ' ') !== false) {
            return explode(' ', $timeStr)[1] ?? null;
        }
        return $timeStr;
    }

    private function timeToMinutes(?string $timeStr): int
    {
        if (!$timeStr) {
            return 0;
        }
        $parts = explode(':', $timeStr);
        return ((int) ($parts[0] ?? 0) * 60) + (int) ($parts[1] ?? 0);
    }
}
