<?php

namespace App\Http\Controllers\software;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Employee;
use App\Models\LeaveApplication;
use App\Models\LeaveType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\Rule;
use App\Exports\LeaveSummaryReportExport;
use Maatwebsite\Excel\Facades\Excel;

class LeaveSummaryReportController extends Controller
{
    public $modules = [];

    public function __construct()
    {
        parent::__construct();

        $this->modules = [
            'title' => 'Leave Summary Report',
            'folder_path' => 'software.modules.reports.leave-summary-report',
            'route' => 'leave-summary-report',
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

        $companies = Company::get();
        $employees = [];
        if ($modules['company_id']) {
            $contractTypeIds = \App\Models\EmployeeType::where('name', 'like', '%contract%')->pluck('id');
            $empQuery = Employee::where('company_id', $modules['company_id'])->where('status', 'active');
            if ($contractTypeIds->isNotEmpty()) {
                $empQuery->whereDoesntHave('employmentDetail', function ($q) use ($contractTypeIds) {
                    $q->whereIn('employment_type', $contractTypeIds);
                });
            }
            $employees = $empQuery->get();
        }

        return view($modules['folder_path'] . '.index', compact('companies', 'employees'));
    }

    public function getReport(Request $request)
    {
        $data = $this->fetchReportData($request);

        if (isset($data['error'])) {
            return response()->json(['status' => 'error', 'message' => $data['message']]);
        }

        $html = View::make($this->modules['folder_path'] . '.report-table', $data)->render();

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
        return view($this->modules['folder_path'] . '.print', array_merge($data, ['is_excel' => false]));
    }

    public function exportExcel(Request $request)
    {
        $data = $this->fetchReportData($request);
        if (isset($data['error'])) {
            return redirect()->back()->withErrors($data['message']);
        }
        return Excel::download(new LeaveSummaryReportExport($data, $this->modules), 'Leave-Summary-Report-' . date('Y-m-d-H-i-s') . '.xlsx');
    }

    private function fetchReportData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => ['required', Rule::exists((new Company())->getTable(), 'id')],
            'employee_id' => ['nullable', Rule::exists((new Employee())->getTable(), 'id')],
            'year' => ['required', 'integer'],
            'month' => ['nullable', 'integer', 'between:1,12'],
            'department_id' => ['nullable', 'integer'],
            'designation_id' => ['nullable', 'integer'],
        ]);

        if ($validator->fails()) {
            return ['error' => true, 'message' => $validator->errors()->first()];
        }

        try {
            $companyId = $request->company_id;
            $employeeId = $request->employee_id;
            $year = $request->year;
            $month = $request->month;

            // Fetch Leave Types
            $leaveTypes = LeaveType::where('company_id', $companyId)
                ->where('status', 'active')
                ->get();

            // Fetch Employees
            $contractTypeIds = \App\Models\EmployeeType::where('name', 'like', '%contract%')->pluck('id');
            $empQuery = Employee::where('company_id', $companyId)
                ->where('status', 'active');
            if ($contractTypeIds->isNotEmpty()) {
                $empQuery->whereDoesntHave('employmentDetail', function ($q) use ($contractTypeIds) {
                    $q->whereIn('employment_type', $contractTypeIds);
                });
            }

            if ($employeeId) {
                $empQuery->where('id', $employeeId);
            }

            if ($request->filled('department_id')) {
                $empQuery->whereHas('employmentDetail', function ($q) use ($request) {
                    $q->where('department_id', $request->department_id);
                });
            }

            if ($request->filled('designation_id')) {
                $empQuery->whereHas('employmentDetail', function ($q) use ($request) {
                    $q->where('designation_id', $request->designation_id);
                });
            }

            $employees = $empQuery->with('employmentDetail')->orderBy('first_name', 'ASC')->get();

            // Fetch Leave Applications (status: 'approved')
            $appsQuery = LeaveApplication::where('company_id', $companyId)
                ->where('status', 'approved');

            if ($month) {
                $monthStart = Carbon::create($year, $month, 1)->startOfDay();
                $monthEnd = Carbon::create($year, $month, 1)->endOfMonth()->endOfDay();
                $appsQuery->whereBetween('fromdate_time', [$monthStart, $monthEnd]);
            } else {
                $fyStart = Carbon::create($year, 4, 1)->startOfDay();
                $fyEnd = Carbon::create($year + 1, 3, 31)->endOfDay();
                $today = Carbon::now()->endOfDay();
                if ($fyEnd->gt($today)) {
                    $fyEnd = $today;
                }
                $appsQuery->whereBetween('fromdate_time', [$fyStart, $fyEnd]);
            }

            if ($employeeId) {
                $appsQuery->where('employee_id', $employeeId);
            }

            $applications = $appsQuery->get();

            // Used leaves from FY start to selected month end (for carry forward month-wise report)
            $usedLeavesFyToMonth = [];
            if ($month) {
                if ($month >= 4) {
                    $fyStartForUsed = Carbon::create($year, 4, 1)->startOfDay();
                } else {
                    $fyStartForUsed = Carbon::create($year - 1, 4, 1)->startOfDay();
                }
                $monthEndForUsed = Carbon::create($year, $month, 1)->endOfMonth()->endOfDay();

                $fyAppsQuery = LeaveApplication::where('company_id', $companyId)
                    ->where('status', 'approved')
                    ->whereBetween('fromdate_time', [$fyStartForUsed, $monthEndForUsed]);

                if ($employeeId) {
                    $fyAppsQuery->where('employee_id', $employeeId);
                }

                foreach ($fyAppsQuery->get() as $app) {
                    $empId = $app->employee_id;
                    $ltId = $app->leave_type_id;

                    $days = 0.5;
                    if ($app->halfday_fullday === 'fullday') {
                        try {
                            $fromDate = Carbon::parse($app->fromdate_time)->startOfDay();
                            $toDate = $app->todate_time ? Carbon::parse($app->todate_time)->startOfDay() : $fromDate;
                            $days = $fromDate->diffInDays($toDate) + 1;
                        } catch (\Exception $e) {
                            $days = 1;
                        }
                    }

                    if (!isset($usedLeavesFyToMonth[$empId])) {
                        $usedLeavesFyToMonth[$empId] = [];
                    }
                    if (!isset($usedLeavesFyToMonth[$empId][$ltId])) {
                        $usedLeavesFyToMonth[$empId][$ltId] = 0;
                    }
                    $usedLeavesFyToMonth[$empId][$ltId] += $days;
                }
            }

            // Calculate used leaves
            $usedLeaves = [];
            foreach ($applications as $app) {
                $empId = $app->employee_id;
                $ltId = $app->leave_type_id;

                $days = 0.5;
                if ($app->halfday_fullday === 'fullday') {
                    try {
                        $fromDate = Carbon::parse($app->fromdate_time)->startOfDay();
                        $toDate = $app->todate_time ? Carbon::parse($app->todate_time)->startOfDay() : $fromDate;
                        $days = $fromDate->diffInDays($toDate) + 1;
                    } catch (\Exception $e) {
                        $days = 1;
                    }
                }

                if (!isset($usedLeaves[$empId])) {
                    $usedLeaves[$empId] = [];
                }
                if (!isset($usedLeaves[$empId][$ltId])) {
                    $usedLeaves[$empId][$ltId] = 0;
                }
                $usedLeaves[$empId][$ltId] += $days;
            }

            $company = Company::find($companyId);




            $startDateStr = null;
            $endDateStr = null;
            if ($month) {
                $startDateStr = Carbon::create($year, $month, 1)->startOfDay()->toDateString();
                $endDateStr = Carbon::create($year, $month, 1)->endOfMonth()->endOfDay()->toDateString();
            } else {
                $startDateStr = Carbon::create($year, 4, 1)->startOfDay()->toDateString();
                $fyEnd = Carbon::create($year + 1, 3, 31)->endOfDay();
                $today = Carbon::now()->endOfDay();
                // All Months = April to current date (or FY end if FY already completed)
                $periodEnd = $fyEnd->gt($today) ? $today : $fyEnd;
                $endDateStr = $periodEnd->toDateString();
            }

            $earnedCoff = [];
            $pendingCoff = [];
            $usedCoff = [];
            $activeMonthsRatio = [];
            foreach ($employees as $emp) {
                $empStartDate = $startDateStr;
                $doj = $emp->employmentDetail?->date_of_joining;
                if ($doj) {
                    try {
                        $parsedDoj = \Carbon\Carbon::parse($doj)->startOfDay()->toDateString();
                        if ($parsedDoj > $empStartDate) {
                            $empStartDate = $parsedDoj;
                        }
                    } catch (\Exception $e) {
                    }
                }

                $empEndDate = $endDateStr;
                $parsedEndDate = \Carbon\Carbon::parse($endDateStr)->endOfDay();

                // Calculate ratio of active months for regular leaves
                $ratio = 1.0;
                $periodStart = \Carbon\Carbon::parse($startDateStr)->startOfDay();

                try {
                    $calculationStart = $periodStart->copy();
                    if ($doj) {
                        $dojDate = \Carbon\Carbon::parse($doj)->startOfDay();
                        if ($dojDate->gt($parsedEndDate)) {
                            $ratio = 0;
                            $calculationStart = null;
                        } elseif ($dojDate->gt($periodStart)) {
                            $calculationStart = $dojDate->copy();
                        }
                    }

                    if ($calculationStart && !$month) {
                        // All months: calculate active months using HR rule: >=16 days = 1 month, <=15 days = 0.5 month
                        $current = $calculationStart->copy()->startOfDay();
                        $end = $parsedEndDate->copy()->endOfDay();

                        $totalActiveMonths = 0;
                        // To avoid infinite loops, set a reasonable limit
                        $loopCount = 0;
                        while ($current->format('Y-m') <= $end->format('Y-m') && $loopCount < 24) {
                            $monthStart = $current->copy()->startOfMonth();
                            $monthEnd = $current->copy()->endOfMonth();

                            $activeStart = max($current, $monthStart);
                            $activeEnd = min($end, $monthEnd);

                            $daysActive = $activeStart->diffInDays($activeEnd) + 1;

                            if ($daysActive >= 16) {
                                $totalActiveMonths += 1;
                            } elseif ($daysActive >= 1 && $daysActive <= 15) {
                                $totalActiveMonths += 0.5;
                            }

                            $current->addMonth()->startOfMonth();
                            $loopCount++;
                        }

                        $totalPeriodMonths = 12; // Yearly quota is based on 12 months
                        $ratio = min(1.0, max(0, $totalActiveMonths / $totalPeriodMonths));
                    }
                } catch (\Exception $e) {
                }

                $activeMonthsRatio[$emp->id] = $ratio;

                // Pass false as the $asOfDate to bypass expiry check in reports, and pass startDate and endDate for filtering
                $earnedCoff[$emp->id] = $emp->getEarnedCoffCount(false, null, $empStartDate, $empEndDate);
                $pendingCoff[$emp->id] = $emp->getAvailableCoffCount(false, null, $empStartDate, $empEndDate);
                $usedCoff[$emp->id] = $emp->getUsedCoffCount(false, null, $empStartDate, $empEndDate);
            }

            $selected_department = null;
            if ($request->filled('department_id')) {
                $selected_department = \App\Models\Department::find($request->department_id)?->name;
            }

            $selected_designation = null;
            if ($request->filled('designation_id')) {
                $selected_designation = \App\Models\Designation::find($request->designation_id)?->name;
            }

            return [
                'leaveTypes' => $leaveTypes,
                'employees' => $employees,
                'usedLeaves' => $usedLeaves,
                'usedLeavesFyToMonth' => $usedLeavesFyToMonth,
                'earnedCoff' => $earnedCoff,
                'pendingCoff' => $pendingCoff,
                'usedCoff' => $usedCoff,
                'activeMonthsRatio' => $activeMonthsRatio,
                'year' => $year,
                'month' => $month,
                'isMonthWise' => !empty($month),
                'endDateStr' => $endDateStr,
                'company' => $company,
                'selected_department' => $selected_department,
                'selected_designation' => $selected_designation,
            ];

        } catch (\Exception $e) {
            return ['error' => true, 'message' => $e->getMessage()];
        }
    }
}
