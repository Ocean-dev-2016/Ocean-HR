<?php

namespace App\Http\Controllers\software;

use App\Exports\SalaryExport;
use App\Exports\SalaryBankTransferExport;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Bonus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeWiseSalaryDetail;
use App\Models\Holiday;
use App\Models\LeaveApplication;
use App\Models\LeaveType;
use App\Models\Loan;
use App\Models\LoanRepayments;
use App\Models\OperationsRateList;
use App\Models\Salary;
use App\Models\BiometricMachine;
use App\Services\Biometric\BiometricServiceFactory;

use Illuminate\Http\Request;

use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

class SalaryCalculationController extends Controller
{
    public $modules = [];

    public function __construct()
    {
        parent::__construct();

        $this->modules = [
            'title' => 'Salary Calculation',
            'folder_path' => 'software.modules.employee.salary-calculation',
            'route' => 'salary-calculation',
            'table_name' => (new Salary())->getTable(),
            'permisstion_prefix' => 'salary-calculation',
            'module_name' => 'Salary Calculation',
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

        View::share('modules', $modules);

        try {
            $columns = [
                (object) ['data' => 'employee.employee_code', 'name' => 'employee.employee_code', 'td_label' => 'Employee Code', 'orderable' => false, 'searchable' => false],
                (object) ['data' => "company_name", 'name' => 'company_id', 'td_label' => 'Company Name', 'className' => ''],

                (object) ['data' => "employee_full_name", 'name' => 'employee_id', 'td_label' => 'Employee Name', 'className' => 'w-10 text-start'],
                (object) ['data' => "year_month", 'name' => 'year', 'td_label' => 'Period', 'className' => ''],
                (object) ['data' => "ctc", 'name' => 'ctc', 'td_label' => 'CTC', 'className' => ''],
                (object) ['data' => "total_earning", 'name' => 'total_earning', 'td_label' => 'Total Earning', 'className' => ''],
                (object) ['data' => "total_deduction", 'name' => 'total_deduction', 'td_label' => 'Total Deduction', 'className' => ''],
                (object) ['data' => "net_bank_pay", 'name' => 'net_bank_pay', 'td_label' => 'Net Pay', 'className' => ''],
                (object) ['data' => "data_integrity_status", 'name' => 'data_modified', 'td_label' => 'Data Status', 'className' => 'w-5 text-center'],
                (object) ['data' => "lock_status", 'name' => 'is_locked', 'td_label' => 'Lock Status', 'className' => 'w-5 text-center'],
                // (object)['data' => "status", 'name' => 'status', 'td_label' => 'Status', 'className' => 'w-5 text-start'],
                (object) ['data' => "action", 'name' => 'action', 'td_label' => 'Action', 'orderable' => false, 'searchable' => false, 'className' => 'w-10 text-start'],
            ];

            if ($modules['company_id']) {
                $columns = array_filter($columns, function ($col) {
                    return $col->name !== 'company_id';
                });
                $columns = array_values($columns);
            }

            View::share("columns", $columns);

            if ($request->ajax()) {
                $data = Salary::with(['company', 'employee', 'branch', 'department'])
                    ->where(function ($query) use ($modules, $loginUserId) {
                        if (Auth::guard('employees')->check() || !empty($modules['company_id'])) {
                            $companyId = $modules['company_id'] ?? Auth::guard('employees')->user()->company_id;
                            $query->where('company_id', $companyId);

                            if (!empty($modules['personal_data_permission']) && empty($modules['all_data_permission'])) {
                                $query->where('created_by', $loginUserId);
                            }
                        }
                    })
                    ->orderBy('id', 'DESC');

                if (isset($modules['restore_permission']) && $modules['restore_permission']) {
                    $data = $data->withTrashed();
                }

                $returnData = Datatables::of($data)
                    ->addIndexColumn()
                    ->filter(function ($query) use ($request) {
                        if ($request->has('filter_company') && $request->filter_company) {
                            $query->where('company_id', $request->filter_company);
                        }
                        if ($request->has('filter_branch') && $request->filter_branch) {
                            $query->where('branch_id', $request->filter_branch);
                        }
                        if ($request->has('filter_department') && $request->filter_department) {
                            $query->where('department_id', $request->filter_department);
                        }
                        if ($request->has('filter_employee') && $request->filter_employee) {
                            $query->where('employee_id', $request->filter_employee);
                        }
                        if ($request->has('filter_year') && $request->filter_year) {
                            $query->where('year', $request->filter_year);
                        }
                        if ($request->has('filter_month') && $request->filter_month) {
                            $query->where('month', $request->filter_month);
                        }
                        if ($request->has('status') && $request->status !== null && $request->status !== 'all') {
                            $query->where((new Salary())->getTable() . '.status', $request->status);
                        }
                    })
                    ->addColumn('company_name', function ($row) {
                        return $row?->company?->company_name ?? '-';
                    })

                    ->addColumn('year_month', function ($row) {
                        $months = [
                            1 => 'Jan',
                            2 => 'Feb',
                            3 => 'Mar',
                            4 => 'Apr',
                            5 => 'May',
                            6 => 'Jun',
                            7 => 'Jul',
                            8 => 'Aug',
                            9 => 'Sep',
                            10 => 'Oct',
                            11 => 'Nov',
                            12 => 'Dec'
                        ];
                        $monthName = $months[$row->month] ?? $row->month;
                        return $monthName . ' ' . $row->year;
                    })
                    ->editColumn('ctc', function ($row) {
                        return number_format($row->ctc ?? 0, 2);
                    })
                    ->editColumn('total_earning', function ($row) {
                        return number_format($row->total_earning ?? 0, 2);
                    })
                    ->editColumn('total_deduction', function ($row) {
                        return number_format($row->total_deduction ?? 0, 2);
                    })
                    ->editColumn('net_bank_pay', function ($row) {
                        return '<span class="badge bg-success">' . number_format($row->net_bank_pay ?? 0, 2) . '</span>';
                    })
                    ->editColumn('status', function ($row) use ($modules) {
                        $btn = '';
                        $dropdown = "";
                        $dropdown .= '<ul class="dropdown-menu" style="">';

                        if ($row->status == "active") {
                            $btn .= '<button type="button" class="btn btn-success btn-sm dropdown-toggle waves-effect waves-light" data-bs-toggle="dropdown" aria-expanded="false">' . ucfirst($row->status) . '</button>';
                            $dropdown .= '<li><a href="javascript:void(0)" class="dropdown-item waves-effect btn-label-danger update-status" data-url="' . route($modules['route'] . '.status-update') . '" data-id="' . $row["id"] . '" data-update_status="inactive">Inactive</a></li>';
                        } elseif ($row->status == "inactive") {
                            $btn .= '<button type="button" class="btn btn-danger btn-sm dropdown-toggle waves-effect waves-light" data-bs-toggle="dropdown" aria-expanded="false">' . ucfirst($row->status) . '</button>';
                            $dropdown .= '<li><a href="javascript:void(0)" class="dropdown-item waves-effect btn-label-success update-status" data-url="' . route($modules['route'] . '.status-update') . '" data-id="' . $row["id"] . '" data-update_status="active">Active</a></li>';
                        } else {
                            return $btn;
                        }
                        $dropdown .= '</ul>';
                        $btn .= $dropdown;
                        return $btn;
                    })
                    ->addColumn('employee_full_name', function ($row) {
                        return $row?->employee?->full_name ?? '-';
                    })
                    ->addColumn('data_integrity_status', function ($row) {
                        // Check data integrity on the fly
                        $row->checkDataIntegrity();

                        if ($row->data_modified) {
                            $modifications = $row->data_modification_details ?? [];
                            $modificationText = [];
                            if (isset($modifications['attendance']) && $modifications['attendance']['modified']) {
                                $modificationText[] = 'Attendance';
                            }
                            if (isset($modifications['leave']) && $modifications['leave']['modified']) {
                                $modificationText[] = 'Leave';
                            }
                            if (isset($modifications['holiday']) && $modifications['holiday']['modified']) {
                                $modificationText[] = 'Holiday';
                            }
                            if (isset($modifications['salary_detail']) && $modifications['salary_detail']['modified']) {
                                $modificationText[] = 'Salary Detail';
                            }

                            $tooltip = 'Modified: ' . implode(', ', $modificationText);
                            return '<span class="badge bg-warning" data-bs-toggle="tooltip" data-bs-placement="top" title="' . htmlspecialchars($tooltip) . '">
                                <i class="fa fa-exclamation-triangle me-1"></i> Modified
                            </span>';
                        } else {
                            return '<span class="badge bg-success" data-bs-toggle="tooltip" data-bs-placement="top" title="Source data unchanged">
                                <i class="fa fa-check-circle me-1"></i> Valid
                            </span>';
                        }
                    })
                    ->addColumn('lock_status', function ($row) use ($modules) {
                        if ($row->is_locked) {
                            $lockedBy = $row->locked_by ? ' by User ID: ' . $row->locked_by : '';
                            $lockedAt = $row->locked_at ? ' on ' . $row->locked_at->format('Y-m-d H:i') : '';
                            return '<span class="badge bg-danger" data-bs-toggle="tooltip" data-bs-placement="top" title="Locked' . $lockedBy . $lockedAt . '">
                                <i class="fa fa-lock me-1"></i> Locked
                            </span>';
                        } else {
                            return '<span class="badge bg-secondary" data-bs-toggle="tooltip" data-bs-placement="top" title="Not locked">
                                <i class="fa fa-unlock me-1"></i> Unlocked
                            </span>';
                        }
                    })
                    ->addColumn('action', function ($row) use ($modules) {
                        $btn = '';
                        if (!$row?->deleted_at) {
                            // Check integrity button
                            $btn .= '<a href="javascript:void(0)" class="btn btn-info btn-icon mx-1 check-integrity-btn" data-salary-id="' . $row->id . '" data-bs-toggle="tooltip" data-bs-placement="top" title="Check Data Integrity">
                                <i class="fa fa-shield-alt"></i>
                            </a>';

                            // Lock/Unlock button
                            if ($row->is_locked) {
                                $btn .= '<a href="javascript:void(0)" class="btn btn-warning btn-icon mx-1 unlock-salary-btn" data-salary-id="' . $row->id . '" data-bs-toggle="tooltip" data-bs-placement="top" title="Unlock Salary">
                                    <i class="fa fa-unlock"></i>
                                </a>';
                            } else {
                                $btn .= '<a href="javascript:void(0)" class="btn btn-secondary btn-icon mx-1 lock-salary-btn" data-salary-id="' . $row->id . '" data-bs-toggle="tooltip" data-bs-placement="top" title="Lock Salary">
                                    <i class="fa fa-lock"></i>
                                </a>';
                            }

                            $isContractor = ($row?->employee?->employment_type === 'Contractor Salary' || str_starts_with($row?->employee?->employee_code, 'CO'));

                            if ($isContractor) {
                                // For Contractor Employee: Show ONLY View button
                                $btn .= '<a href="' . route($modules["route"] . ".edit", [$row["id"], "view_mode" => 1]) . '" class="btn btn-info btn-icon mx-1" data-bs-toggle="tooltip" data-bs-placement="top" title="View Salary Calculation"><i class="fa-solid fa-eye"></i></a>';
                            } else {
                                // For Normal Employee: Show Edit and Delete buttons as usual
                                if ($modules['update_permission'] && !$row->is_locked) {
                                    $btn .= '<a href="' . route($modules["route"] . ".edit", [$row["id"]]) . '" class="btn btn-light btn-icon mx-1"><i class="fa-solid fa-pen-to-square"></i></a>';
                                }
                                if ($modules['delete_permission'] && !$row->is_locked) {
                                    $btn .= '<a href="javascript:void(0)" data-id="' . $row->id . '" data-did="' . route($modules["route"] . ".destroy", [$row["id"]]) . '" class="btn btn-danger btn-icon deletebutton mx-1"><i class="fa-solid fa-trash"></i></a>';
                                }
                            }
                        } else {
                            $btn .= '<a href="javascript:void(0)" data-restore="' . route($modules["route"] . ".restore", ['id' => $row["id"]]) . '" class="btn btn-light mx-1 record-restore" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Restore Data"><i class="ti ti-history"></i> Restore</a>';
                        }
                        if ($btn == '') {
                            $btn = '-';
                        }
                        return $btn;
                    })
                    ->rawColumns(['status', 'action', 'net_bank_pay', 'data_integrity_status', 'lock_status'])
                    ->make(true);
                return $returnData;
            }

            return view($modules['folder_path'] . '.index');
        } catch (\Exception $e) {
            return Redirect::route('software.dashboard')->withErrors($e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $modules = $this->modules;
        $modules['addPermission'] = Gate::check('hasPermission', ['add', $modules['module_name']]);
        $modules['authLoginUserDetail'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails : null;
        $modules['company_id'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails?->company_id : null;
        $modules['parent_type_id'] = ($this->authenticateLoginUserDetails?->parent_type_id) ? $this->authenticateLoginUserDetails?->parent_type_id : null;
        $loginUserId = ($modules['authLoginUserDetail'] && $modules['authLoginUserDetail']?->id) ? $modules['authLoginUserDetail']?->id : null;

        if (count(config('constants.permissions'))) {
            foreach (config('constants.permissions') as $key => $value) {
                $modules[$value . '_permission'] = (isset($modules['company_id']) && !$modules['company_id']) ? true : Gate::check('hasPermission', [$value, $modules['module_name']]);
            }
        }

        if (!$modules['add_permission']) {
            if (isset($request) && $request->ajax()) {
                return $this->sendError('Unauthorized', [], [], 403);
            }
            abort(403, 'Unauthorized');
        }

        try {
            View::share('modules', $modules);
            return view($modules['folder_path'] . '.form');
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }

    /**
     * Calculate salary for employee based on filters
     */
    public function calculateSalary(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => ['required', 'exists:' . (new Company())->getTable() . ',id'],
            'branch_id' => ['nullable', 'exists:' . (new Branch())->getTable() . ',id'],
            'department_id' => ['nullable', 'exists:' . (new Department())->getTable() . ',id'],
            'employee_id' => ['required', 'exists:' . (new Employee())->getTable() . ',id'],
            'year' => ['required', 'integer', 'min:2020', 'max:2050'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->messages()->first(),
                'errors' => $validator->messages()
            ], 422);
        }

        try {
            $companyId = $request->company_id;
            // Security: Enforce company_id for restricted users
            if ($this->authenticateLoginUserDetails && $this->authenticateLoginUserDetails->company_id) {
                $companyId = $this->authenticateLoginUserDetails->company_id;
            }
            $branchId = $request->branch_id;
            $employeeId = $request->employee_id;
            $year = (int) $request->year;
            $month = (int) $request->month;

            // Get employee details
            $employee = Employee::with(['branch'])->find($employeeId);
            if (!$employee) {
                return response()->json(['success' => false, 'message' => 'Employee not found'], 404);
            }

            // Get salary details for employee (dynamically applying any active increment for the month/year)
            $salaryDetail = $this->getApplicableSalaryDetail($companyId, $employeeId, $year, $month);

            if (!$salaryDetail) {
                return response()->json([
                    'success' => false,
                    'message' => 'Salary details not found for this employee. Please configure salary details first.'
                ], 404);
            }

            // Calculate date range for the month
            $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
            $today = Carbon::today();
            $totalDaysInMonth = $startDate->daysInMonth;

            // Get holiday dates & leave dates (For Exclusion Logic)
            $holidayDates = $this->getHolidayDates($companyId, $startDate, $endDate);
            $leaveDates = $this->getLeaveDates($companyId, $employeeId, $startDate, $endDate);

            // Get attendance data (Passing exclusions)
            $attendanceData = $this->getAttendanceData($companyId, $employeeId, $startDate, $endDate, $salaryDetail, $holidayDates, $leaveDates);

            // Get holiday count
            $holidayCount = $this->getHolidayCount($companyId, $startDate, $endDate);

            // Get leave data
            $leaveData = $this->getLeaveData($companyId, $employeeId, $startDate, $endDate);

            // Get bonus data
            $bonusData = $this->getBonusData($companyId, $employeeId, $branchId, $year, $month);

            // Get loan EMI data
            $loanData = $this->getLoanData($companyId, $employeeId);

            // Calculate per day salary
            $ctc = $salaryDetail->ctc ?? 0;

            // Determine divisor based on configuration
            $monthCountType = trim($salaryDetail->salary_calculation_month_count ?? '');

            // Normalize for comparison
            $monthCountTypeNormalized = strtolower($monthCountType);

            // Default to 30
            $salaryDivisor = 30;

            if (str_contains($monthCountTypeNormalized, 'total days')) {
                // Matches 'Per Month Total Days' and 'Per Month Total Days - Week Off'
                $salaryDivisor = $totalDaysInMonth;

                // If it is specifically 'week off' variant, we still need to calculate wek off count for other logic, but divisor remains TotalDays as per latest requirement.
                if (str_contains($monthCountTypeNormalized, 'week off')) {
                    // logic to count week offs (already present below, just ensure it runs)
                }
            } elseif (str_contains($monthCountTypeNormalized, 'fix 30')) {
                $salaryDivisor = 30;
            } else {
                // Fallback: If unknown, default to 30, OR should we default to total days?
                // User seems to prefer Real Days logic often. Let's stick to 30 as safe default but maybe the string is empty?
                $salaryDivisor = 30;
            }

            // Original Week Off Logic (Calculates count, but does NOT reduce divisor anymore)
            if ($monthCountType === 'Per Month Total Days - Week Off' || str_contains($monthCountTypeNormalized, 'week off')) {
                // Calculate total week offs in the entire month
                $totalWeekOffs = 0;
                $weekOffDays = [];
                if ($salaryDetail && $salaryDetail->week_off) {
                    $weekOffArray = json_decode($salaryDetail->week_off, true);
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
                    if (is_array($weekOffArray)) {
                        foreach ($weekOffArray as $wo) {
                            $woStr = strtolower(trim($wo));
                            if (isset($dayMap[$woStr])) {
                                $weekOffDays[] = $dayMap[$woStr];
                            }
                        }
                    }
                }

                for ($d = 1; $d <= $totalDaysInMonth; $d++) {
                    $currentDay = Carbon::createFromDate($year, $month, $d);
                    if (in_array($currentDay->dayOfWeek, $weekOffDays)) {
                        $totalWeekOffs++;
                    }
                }

                // Divisor is Total Days - Week Offs
                $salaryDivisor = $totalDaysInMonth - $totalWeekOffs;
            }

            // Avoid division by zero
            if ($salaryDivisor <= 0)
                $salaryDivisor = 30;

            $perDaySalary = $ctc / $salaryDivisor;


            // Get operation earnings total
            $operationEarnings = OperationsRateList::where('company_id', $companyId)
                ->where('employee_id', $employeeId)
                ->where('month', $month)
                ->where('year', $year)
                ->sum('total_amount');

            // Calculate salary components
            $calculatedData = $this->calculateSalaryComponents(
                $salaryDetail,
                $attendanceData,
                $holidayCount,
                $leaveData,
                $bonusData,
                $loanData,
                $totalDaysInMonth,
                $perDaySalary,
                $salaryDivisor,
                $employee,
                $startDate,
                $endDate,
                $operationEarnings
            );

            Log::info('Salary Calculation Debug', [
                'month_count_type' => $monthCountType,
                'salary_divisor' => $salaryDivisor,
                'total_days_in_month' => $totalDaysInMonth,
                'ctc' => $ctc
            ]);

            return response()->json([
                'success' => true,
                'data' => $calculatedData,
                'employee' => $employee,
                'salary_detail' => $salaryDetail,
                'debug' => [
                    'month_count_type' => $monthCountType,
                    'salary_divisor' => $salaryDivisor,
                    'total_days_in_month' => $totalDaysInMonth,
                    'ctc' => $ctc
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error calculating salary: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine()
            ], 500);
        }
    }

    /**
     * Get raw attendance summary for view mode (works without EmployeeWiseSalaryDetail)
     */
    public function getAttendanceSummary(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => ['required'],
            'employee_id' => ['required'],
            'year' => ['required', 'integer'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->messages()->first()
            ], 422);
        }

        try {
            $companyId = $request->company_id;
            if ($this->authenticateLoginUserDetails && $this->authenticateLoginUserDetails->company_id) {
                $companyId = $this->authenticateLoginUserDetails->company_id;
            }
            $employeeId = $request->employee_id;
            $year = (int) $request->year;
            $month = (int) $request->month;

            $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
            $today = Carbon::today();
            $totalDaysInMonth = $startDate->daysInMonth;

            // Get all attendance records
            $attendances = Attendance::with('shift')
                ->where('company_id', $companyId)
                ->where('employee_id', $employeeId)
                ->whereBetween('attendance_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->get();

            $attendanceByDate = $attendances->groupBy('attendance_date');

            // Get holiday dates
            $holidayDates = $this->getHolidayDates($companyId, $startDate, $endDate);
            $holidayCount = $this->getHolidayCount($companyId, $startDate, $endDate);

            // Get leave data
            $leaveData = $this->getLeaveData($companyId, $employeeId, $startDate, $endDate);
            $leaveDates = $this->getLeaveDates($companyId, $employeeId, $startDate, $endDate);

            // Try to get salary detail for week off info (optional)
            $salaryDetail = EmployeeWiseSalaryDetail::where('company_id', $companyId)
                ->where('employee_id', $employeeId)
                ->first();

            // Get week off days
            $weekOffDays = [];
            if ($salaryDetail && $salaryDetail->week_off) {
                $weekOffArray = json_decode($salaryDetail->week_off, true);
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
                if (is_array($weekOffArray)) {
                    foreach ($weekOffArray as $wo) {
                        $woStr = strtolower(trim($wo));
                        if (isset($dayMap[$woStr])) {
                            $weekOffDays[] = $dayMap[$woStr];
                        }
                    }
                }
            }

            // Count attendance metrics
            $presentDays = 0;
            $halfDays = 0;
            $absentDays = 0;
            $weekOffCount = 0;
            $weekOffWorkingDays = 0;
            $totalWorkingMinutes = 0;
            $holidayDaysCount = 0;
            $usedGraceDay = false;

            for ($day = $startDate->copy(); $day->lte($endDate); $day->addDay()) {
                $dateStr = $day->format('Y-m-d');
                $dayOfWeek = $day->dayOfWeek;
                $isWeekOff = in_array((int) $dayOfWeek, $weekOffDays, true);
                $isHoliday = isset($holidayDates[$dateStr]);
                $isLeave = isset($leaveDates[$dateStr]);

                $dayRecords = $attendanceByDate->get($dateStr, collect());
                $hasPunches = $dayRecords->count() > 0;

                // Compute worked minutes
                $workedMinutes = 0;
                if ($hasPunches) {
                    $allPunches = $dayRecords->map(function ($r) {
                        return [
                            'type' => $r->attendace_type,
                            'time' => $this->extractTime($r->punch_in_time),
                            'full_time' => $r->punch_in_time
                        ];
                    })->sortBy('time')->values()->toArray();

                    for ($i = 0; $i < count($allPunches); $i++) {
                        if (
                            strtolower($allPunches[$i]['type']) === 'in' &&
                            isset($allPunches[$i + 1]) &&
                            strtolower($allPunches[$i + 1]['type']) === 'out'
                        ) {
                            $workedMinutes += $this->calculateMinutesDiff($allPunches[$i]['full_time'], $allPunches[$i + 1]['full_time']);
                            $i++;
                        }
                    }
                    // Subtract break if shift info available
                    $firstRecord = $dayRecords->sortBy('punch_in_time')->first();
                    if ($firstRecord && $firstRecord->shift && $firstRecord->shift->breaking_hour) {
                        $workedMinutes = max(0, $workedMinutes - $this->timeToMinutes($firstRecord->shift->breaking_hour));
                    }
                }

                if ($isLeave) {
                    continue; // Leave counted separately
                } elseif ($isHoliday) {
                    $holidayDaysCount++;
                } elseif ($isWeekOff) {
                    if ($hasPunches && $workedMinutes > 0) {
                        $weekOffWorkingDays++;
                    } else {
                        $weekOffCount++;
                    }
                } elseif ($hasPunches) {
                    // Determine half day vs present
                    $halfDayMinutes = 240; // Default 4 hours
                    $presentDayMinutes = 480; // Default 8 hours
                    $firstRecord = $dayRecords->sortBy('punch_in_time')->first();
                    if ($firstRecord && $firstRecord->shift) {
                        $halfDayMinutes = $this->timeToMinutes($firstRecord->shift->half_day_hour ?? '04:00:00');
                        $presentDayMinutes = $this->timeToMinutes($firstRecord->shift->present_day_hour ?? '08:00:00');
                    }

                    $isHalfDayMarked = $dayRecords->contains(function ($value) {
                        return in_array(strtolower($value->attendace_type), ['half_day', 'half day', 'hd']);
                    });

                    // Late/Early Grace checks
                    $isLateWithinOneHour = false;
                    $isEarlyWithinOneHour = false;
                    $isLateMoreThanOneHour = false;
                    $isEarlyMoreThanOneHour = false;

                    $sortedDayRecords = $dayRecords->sortBy('punch_in_time');
                    $inRecord = $sortedDayRecords->filter(fn($r) => strtolower($r->attendace_type) === 'in')->first() ?? $sortedDayRecords->first();
                    $outRecord = $sortedDayRecords->filter(fn($r) => strtolower($r->attendace_type) === 'out')->last() ?? $sortedDayRecords->last();

                    if ($inRecord && $inRecord->shift) {
                        $shift = $inRecord->shift;
                        if ($shift->punch_in_minimum) {
                            try {
                                $shiftStartTimeStr = $this->extractTime($shift->punch_in_minimum);
                                $firstPunchTimeStr = $this->extractTime($inRecord->punch_in_time);
                                $shiftStartMinutes = $this->timeToMinutes($shiftStartTimeStr);
                                $graceMinutes = (int) ($shift->in_out_grace_period ?? $shift->grace_period ?? 0);
                                $graceLimitMinutes = $shiftStartMinutes + $graceMinutes;

                                $firstPunchMinutes = $this->timeToMinutes($firstPunchTimeStr);
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

                        if ($shift->punch_out && $outRecord) {
                            try {
                                $shiftEndTimeStr = $this->extractTime($shift->punch_out);
                                $lastPunchTimeStr = $this->extractTime($outRecord->punch_in_time);
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

                    if ($isHalfDayMarked) {
                        $halfDays++;
                    } elseif ($workedMinutes < $halfDayMinutes) {
                        $absentDays++;
                    } elseif ($workedMinutes >= $presentDayMinutes || $applyGrace) {
                        $presentDays++;
                    } else {
                        $halfDays++;
                    }

                    $totalWorkingMinutes += $workedMinutes;
                } elseif ($day->lt($today)) {
                    $absentDays++;
                }
            }

            $totalHours = floor($totalWorkingMinutes / 60);
            $totalMins = $totalWorkingMinutes % 60;
            $workingHoursFormatted = sprintf('%02d:%02d:00', $totalHours, $totalMins);

            $calculateDays = $presentDays + ($halfDays * 0.5) + $weekOffCount + $holidayDaysCount + $leaveData['total_company_pay_leave'] + $weekOffWorkingDays;

            return response()->json([
                'success' => true,
                'data' => [
                    'total_day' => $totalDaysInMonth,
                    'calculate_days' => $calculateDays,
                    'total_present_day' => $presentDays,
                    'half_day' => $halfDays,
                    'holiday' => $holidayCount,
                    'total_week_off' => $weekOffCount,
                    'working_week_off' => $weekOffWorkingDays,
                    'total_sandwich_leave' => 0,
                    'total_leave' => $leaveData['total_leave'],
                    'total_company_pay_leave' => $leaveData['total_company_pay_leave'],
                    'total_employee_pay_leave' => $leaveData['total_employee_pay_leave'],
                    'total_absent' => $absentDays,
                    'working_hour' => $workingHoursFormatted,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get attendance data for employee
     */
    private function getAttendanceData($companyId, $employeeId, $startDate, $endDate, $salaryDetail, $holidayDates = [], $leaveDates = [])
    {
        $today = Carbon::today();
        $sandWichRuleEnabled = ($salaryDetail->sandwich_rule_flag == 'yes' || $salaryDetail->sandwich_rule_flag == 1);

        // Get all attendance records
        $attendances = Attendance::with('shift')->where('company_id', $companyId)
            ->where('employee_id', $employeeId)
            ->whereBetween('attendance_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get();

        // Group by date
        $attendanceByDate = $attendances->groupBy('attendance_date');

        $presentDays = 0;
        $halfDays = 0;
        $absentDays = 0;
        $totalWorkingMinutes = 0;
        $totalOtHours = 0;
        $otDays = 0;
        $totalShortfallMinutes = 0;
        $sandwichLeaveCount = 0;
        $holidayDays = 0;

        // Get week off days from salary details
        $weekOffDays = [];
        if ($salaryDetail && $salaryDetail->week_off) {
            $weekOffArray = json_decode($salaryDetail->week_off, true);
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
            if (is_array($weekOffArray)) {
                foreach ($weekOffArray as $wo) {
                    $woStr = strtolower(trim($wo));
                    if (isset($dayMap[$woStr])) {
                        $weekOffDays[] = $dayMap[$woStr];
                    }
                }
            }
        }

        $weekOffCount = 0;
        $weekOffWorkingDays = 0;

        // Build Daily Status Map (same order as Attendance Report: Holiday → Leave → Week Off → Present/Absent)
        $dailyStatus = [];
        $debugWeekOffs = [];

        for ($day = $startDate->copy(); $day->lte($endDate); $day->addDay()) {
            $dateStr = $day->format('Y-m-d');
            $dayOfWeek = $day->dayOfWeek; // 0 (Sun) to 6 (Sat)
            $isWeekOff = in_array((int) $dayOfWeek, $weekOffDays, true); // Strict check with int cast
            $isHoliday = isset($holidayDates[$dateStr]);
            $isLeave = isset($leaveDates[$dateStr]);

            $dayRecords = $attendanceByDate->get($dateStr, collect());
            $hasPunches = $dayRecords->count() > 0;

            // Compute worked minutes for days with punches (same logic as Attendance Report: IN-OUT pairs sorted by time, minus break)
            $workedMinutes = 0;
            if ($hasPunches) {
                $allPunches = $dayRecords->map(function ($r) {
                    $time = $r->punch_in_time;
                    return [
                        'type' => $r->attendace_type,
                        'time' => $this->extractTime($time),
                        'full_time' => $time
                    ];
                })->sortBy('time')->values()->toArray();

                $totalPunchMinutes = 0;
                for ($i = 0; $i < count($allPunches); $i++) {
                    if (
                        strtolower($allPunches[$i]['type']) === 'in' &&
                        isset($allPunches[$i + 1]) &&
                        strtolower($allPunches[$i + 1]['type']) === 'out'
                    ) {
                        $totalPunchMinutes += $this->calculateMinutesDiff($allPunches[$i]['full_time'], $allPunches[$i + 1]['full_time']);
                        $i++;
                    }
                }
                $breakMinutes = 0;
                $sortedDayRecords = $dayRecords->sortBy('punch_in_time');
                $firstRecord = $sortedDayRecords->filter(fn($r) => strtolower($r->attendace_type) === 'in')->first() ?? $sortedDayRecords->first();

                if ($firstRecord && $firstRecord->shift && $firstRecord->shift->breaking_hour) {
                    $breakMinutes = $this->timeToMinutes($firstRecord->shift->breaking_hour);
                }
                $workedMinutes = max(0, $totalPunchMinutes - $breakMinutes);
            }

            $status = 'NA'; // Default for future

            // Same order as AttendanceReportController: Holiday → Leave → Week Off → Present/Absent
            if ($isLeave) {
                $status = 'LEAVE';
            } elseif ($isHoliday) {
                $status = 'HOLIDAY';
            } elseif ($isWeekOff && $day->lte($today)) {
                if ($hasPunches && $workedMinutes > 0) {
                    $status = 'WEEK_OFF_WORKING'; // Worked on week off = payable but not counted as present/half
                } else {
                    $status = 'WEEKOFF';
                    $debugWeekOffs[] = $dateStr;
                }
            } elseif ($hasPunches) {
                $status = 'PRESENT';
            } elseif ($day->lte($today)) {
                $status = 'ABSENT';
            } else {
                $status = 'NA';
            }

            $dailyStatus[$dateStr] = [
                'status' => $status,
                'date' => $day->copy(),
                'records' => $dayRecords,
                'isWeekOff' => $isWeekOff,
                'workedMinutes' => $workedMinutes,
            ];
        }

        // Apply Sandwich Rule
        if ($sandWichRuleEnabled) {
            $appliedOn = strtolower($salaryDetail->sandwich_rule_applied_on ?? 'both'); // both, week off, holiday

            $dates = array_keys($dailyStatus);
            $count = count($dates);
            for ($i = 0; $i < $count; $i++) {
                $currentDate = $dates[$i];
                $curr = $dailyStatus[$currentDate];

                $shouldCheck = false;
                if ($curr['status'] === 'WEEKOFF') {
                    if ($appliedOn === 'both' || $appliedOn === 'week off' || $appliedOn === 'weekoff') {
                        $shouldCheck = true;
                    }
                } elseif ($curr['status'] === 'HOLIDAY') {
                    if ($appliedOn === 'both' || $appliedOn === 'holiday') {
                        $shouldCheck = true;
                    }
                }

                // Only Apply Sandwich if allowed by config
                if ($shouldCheck) {
                    // Check Previous
                    $prevStatus = 'PRESENT'; // Default safe
                    if ($i > 0) {
                        $prevDate = $dates[$i - 1];
                        $prevStatus = $dailyStatus[$prevDate]['status'];
                    }

                    // Check Next
                    $nextStatus = 'PRESENT'; // Default safe
                    if ($i < $count - 1) {
                        $nextDate = $dates[$i + 1];
                        $nextStatus = $dailyStatus[$nextDate]['status'];
                    }

                    // Condition: If surrounded by Absent or Leave
                    $badStatuses = ['ABSENT', 'LEAVE', 'SANDWICH'];
                    // NOTE: Future 'NA' status is NOT a bad status, so Future WeekOffs are protected from Sandwich Rule
                    // MODIFIED: Changed to || (OR) to apply rule if ANY side is bad (Start OR End sandwich).
                    // Original was && (Both sides). User requested to cut payment.
                    if (in_array($prevStatus, $badStatuses) || in_array($nextStatus, $badStatuses)) {
                        $dailyStatus[$currentDate]['status'] = 'SANDWICH';
                        $sandwichLeaveCount++;
                    }
                }
            }
        }

        // Now Calculate metrics based on Final Status
        $usedGraceDay = false;
        foreach ($dailyStatus as $dateStr => $data) {
            $status = $data['status'];
            $dayRecords = $data['records'];
            $isWeekOff = $data['isWeekOff'];

            if ($status === 'WEEKOFF') {
                $weekOffCount++;
                continue;
            }

            // Week off but employee worked: do NOT count in salary calculation
            if ($status === 'WEEK_OFF_WORKING') {
                $weekOffWorkingDays++;
                continue;
            }

            // Fix: Ensure Holiday status is respected and not double counted as Absent
            if ($status === 'HOLIDAY') {
                // Holiday is generally payable, so we don't count as absent.
                // We don't increment presentDays here because calculateDays adds $holidayCount separately.
                $holidayDays++;
                continue;
            }

            if ($status === 'LEAVE') {
                // Leave is counted in getLeaveData
                continue;
            }

            if ($status === 'SANDWICH') {
                // Treated as Absent/Leave.
                continue;
            }

            if ($status === 'ABSENT') {
                $absentDays++;
                continue;
            }

            if ($status === 'PRESENT') {
                // Use same worked minutes as used for status (already computed with sorted IN-OUT pairs and break)
                $dailyMinutes = $data['workedMinutes'];

                // Check if manually marked as Half Day (same as Attendance Report: explicit half_day/hd wins)
                $isHalfDayMarked = $dayRecords->contains(function ($value) {
                    return in_array(strtolower($value->attendace_type), ['half_day', 'half day', 'hd']);
                });

                // Same defaults as AttendanceReportController: present_day_hour ?? '08:00:00', half_day_hour ?? '04:00:00'
                $shiftMinutes = 480; // Default 8 hours (Working Hour)
                $halfDayMinutes = 240; // Default 4 hours
                $presentDayMinutes = 480; // Default 8 hours (Present Day Threshold)
                $firstRecord = $dayRecords->first();

                if ($firstRecord && $firstRecord->shift) {
                    if ($firstRecord->shift->working_hour) {
                        $shiftMinutes = $this->timeToMinutes($firstRecord->shift->working_hour);
                    }
                    $halfDayMinutes = $this->timeToMinutes($firstRecord->shift->half_day_hour ?? '04:00:00');
                    $presentDayMinutes = $this->timeToMinutes($firstRecord->shift->present_day_hour ?? '08:00:00');
                }

                $totalWorkingMinutes += $dailyMinutes;

                // Late/Early Grace checks
                $isLateWithinOneHour = false;
                $isEarlyWithinOneHour = false;
                $isLateMoreThanOneHour = false;
                $isEarlyMoreThanOneHour = false;

                $sortedDayRecords = $dayRecords->sortBy('punch_in_time');
                $inRecord = $sortedDayRecords->filter(fn($r) => strtolower($r->attendace_type) === 'in')->first() ?? $sortedDayRecords->first();
                $outRecord = $sortedDayRecords->filter(fn($r) => strtolower($r->attendace_type) === 'out')->last() ?? $sortedDayRecords->last();

                if ($inRecord && $inRecord->shift) {
                    $shift = $inRecord->shift;
                    if ($shift->punch_in_minimum) {
                        try {
                            $shiftStartTimeStr = $this->extractTime($shift->punch_in_minimum);
                            $firstPunchTimeStr = $this->extractTime($inRecord->punch_in_time);
                            $shiftStartMinutes = $this->timeToMinutes($shiftStartTimeStr);
                            $graceMinutes = (int) ($shift->in_out_grace_period ?? $shift->grace_period ?? 0);
                            $graceLimitMinutes = $shiftStartMinutes + $graceMinutes;

                            $firstPunchMinutes = $this->timeToMinutes($firstPunchTimeStr);
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

                    if ($shift->punch_out && $outRecord) {
                        try {
                            $shiftEndTimeStr = $this->extractTime($shift->punch_out);
                            $lastPunchTimeStr = $this->extractTime($outRecord->punch_in_time);
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

                $forceHalfDayDueToLate = false;
                if ($firstRecord && $firstRecord->shift && $firstRecord->shift->punch_in_minimum && !$applyGrace) {
                    try {
                        $shiftStartTimeStr = $this->extractTime($firstRecord->shift->punch_in_minimum);
                        $firstPunchTimeStr = $this->extractTime($firstRecord->punch_in_time);
                        $shiftStartMinutes = $this->timeToMinutes($shiftStartTimeStr);
                        if ($firstRecord->shift->in_out_grace_period) {
                            $shiftStartMinutes += (int) $firstRecord->shift->in_out_grace_period;
                        } elseif ($firstRecord->shift->grace_period) {
                            $shiftStartMinutes += (int) $firstRecord->shift->grace_period;
                        }

                        $firstPunchMinutes = $this->timeToMinutes($firstPunchTimeStr);
                        if ($firstPunchMinutes > $shiftStartMinutes) {
                            $forceHalfDayDueToLate = true;
                        }
                    } catch (\Exception $e) {
                    }
                }

                // CHANGED: Prioritize Explicit Half Day Check
                if ($isHalfDayMarked) {
                    $halfDays++;
                    // Do NOT increment presentDays
                } else if ($dailyMinutes < $halfDayMinutes) {
                    // Treating miss punch / worked less than half day as half-day LWP (counts under halfDays)
                    $halfDays++;
                    if ($dailyMinutes > 0 && in_array($salaryDetail->overtime, ['yes', '1', 1, true])) {
                        $totalOtHours += ($dailyMinutes / 60);
                    }
                } else if (($dailyMinutes >= $presentDayMinutes || $applyGrace) && !$forceHalfDayDueToLate) {
                    // More than Present Day limit or Grace applied -> Count as Present
                    $presentDays++;

                    // Check for Shortfall (Working less than Shift Hours)
                    if ($dailyMinutes < $shiftMinutes && !$applyGrace) {
                        $shortfall = $shiftMinutes - $dailyMinutes;
                        if ($shortfall > 10) {
                            $totalShortfallMinutes += $shortfall;
                        }
                    }

                    // Calculate OT (Working more than Shift Hours)
                    if ($dailyMinutes > $shiftMinutes && in_array($salaryDetail->overtime, ['yes', '1', 1, true])) {
                        $otMinutes = $dailyMinutes - $shiftMinutes;
                        $totalOtHours += ($otMinutes / 60);
                        $otDays++;
                    }
                } else if ($dailyMinutes >= $halfDayMinutes) {
                    // Between Half Day and Present Day -> Count as Half Day
                    $halfDays++;
                }
            }
        }

        // Convert total working minutes to hours format
        $totalHours = floor($totalWorkingMinutes / 60);
        $totalMins = $totalWorkingMinutes % 60;
        $workingHoursFormatted = sprintf('%02d:%02d:00', $totalHours, $totalMins);

        return [
            'total_present_day' => $presentDays,
            'half_day' => $halfDays,
            'total_absent' => $absentDays,
            'total_week_off' => $weekOffCount,
            'week_off_working_days' => $weekOffWorkingDays,
            'working_hour' => $workingHoursFormatted,
            'total_working_hours_decimal' => round($totalWorkingMinutes / 60, 2),
            'actual_total_ot_hours' => round($totalOtHours, 2),
            'earn_ot_hours' => round($totalOtHours, 2),
            'earn_ot_days' => $otDays,
            'total_shortfall_minutes' => $totalShortfallMinutes,
            'sandwich_leave_count' => $sandwichLeaveCount,
            'total_holiday_days' => $holidayDays
        ];
    }

    /**
     * Get holiday count for the period
     */
    private function getHolidayCount($companyId, $startDate, $endDate)
    {
        // USER REQUEST: Do not count Future Holidays in Payable/Credit days.
        // Clamp endDate to Today if endDate is in future
        $today = Carbon::today();
        $calcEndDate = $endDate->copy();
        if ($calcEndDate->gt($today)) {
            $calcEndDate = $today->copy();
        }

        // If start date is also in future, return 0
        if ($startDate->gt($today)) {
            return 0;
        }

        $holidays = Holiday::where('company_id', $companyId)
            ->where(function ($q) use ($startDate, $calcEndDate) {
                $q->whereBetween('from_date', [$startDate, $calcEndDate])
                    ->orWhereBetween('to_date', [$startDate, $calcEndDate])
                    ->orWhere(fn($q2) => $q2->where('from_date', '<', $startDate)->where('to_date', '>', $calcEndDate));
            })->get();

        $holidayCount = 0;
        foreach ($holidays as $h) {
            $from = Carbon::parse($h->getRawOriginal('from_date'))->startOfDay();
            $to = Carbon::parse($h->getRawOriginal('to_date'))->startOfDay();

            // Clamp to calculation boundaries
            if ($from->lt($startDate))
                $from = $startDate->copy();
            if ($to->gt($calcEndDate))
                $to = $calcEndDate->copy();

            // Ensure valid range
            if ($from->lte($to)) {
                $holidayCount += $from->diffInDays($to) + 1;
            }
        }

        return $holidayCount;
    }

    /**
     * Get leave data for employee
     */
    private function getLeaveData($companyId, $employeeId, $startDate, $endDate)
    {
        $leaves = LeaveApplication::where('company_id', $companyId)
            ->where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('fromdate_time', [$startDate, $endDate])
                    ->orWhereBetween('todate_time', [$startDate, $endDate])
                    ->orWhere(fn($q2) => $q2->where('fromdate_time', '<', $startDate)->where('todate_time', '>', $endDate));
            })
            ->with('leave_type')
            ->get();

        $totalLeave = 0;
        $companyPayLeave = 0;
        $employeePayLeave = 0;
        $sandwichLeave = 0;

        foreach ($leaves as $leave) {
            $from = Carbon::parse($leave->fromdate_time)->startOfDay();
            $to = Carbon::parse($leave->todate_time ?? $leave->fromdate_time)->startOfDay();

            // Clamp to month boundaries
            if ($from->lt($startDate))
                $from = $startDate->copy();
            if ($to->gt($endDate))
                $to = $endDate->copy();

            $leaveDays = $from->diffInDays($to) + 1;

            if ($leave->halfday_fullday === 'halfday') {
                $leaveDays = 0.5;
            }

            $totalLeave += $leaveDays;

            // Check leave type mode (0 = Employee Pay, 1 = Company Pay)
            if ($leave->leave_type && $leave->leave_type->mode == 1) {
                $companyPayLeave += $leaveDays;
            } else {
                $employeePayLeave += $leaveDays;
            }
        }

        return [
            'total_leave' => $totalLeave,
            'total_company_pay_leave' => $companyPayLeave,
            'total_employee_pay_leave' => $employeePayLeave,
            'total_sandwich_leave' => $sandwichLeave,
        ];
    }

    /**
     * Get bonus data for employee
     */
    private function getBonusData($companyId, $employeeId, $branchId, $year, $month)
    {
        $bonus = Bonus::where('company_id', $companyId)
            ->where('year', $year)
            ->where('month', $month)
            ->where(function ($q) use ($employeeId, $branchId) {
                $q->where('employee', $employeeId)
                    ->orWhere('branch', $branchId);
            })
            ->first();

        return [
            'bonus_amount' => $bonus->amount ?? 0,
        ];
    }

    /**
     * Get loan EMI data for employee
     */
    private function getLoanData($companyId, $employeeId)
    {
        $loan = Loan::where('company_id', $companyId)
            ->where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->where('remaining_installments', '>', 0)
            ->first();

        if (!$loan) {
            return [
                'ded_loan_amount' => 0,
                'loan_balance' => 0,
                'loan_id' => null,
                'total_installments' => 0,
                'remaining_installments' => 0,
                'current_installment_no' => 0,
                'loan_info' => 'No active loan'
            ];
        }

        // Fetch next pending repayment
        $pendingRepayment = LoanRepayments::where('loan_id', $loan->id)
            ->where('status', 'pending')
            ->orderBy('due_date', 'asc')
            ->first();

        $emiAmount = 0;
        if ($pendingRepayment) {
            $emiAmount = $pendingRepayment->installment_amount;
        } else {
            $emiAmount = $loan->emi_amount ?? 0;
        }

        // If emiAmount is still 0, fall back to first repayment if exists
        if ($emiAmount <= 0) {
            $firstRepayment = LoanRepayments::where('loan_id', $loan->id)->first();
            if ($firstRepayment) {
                $emiAmount = $firstRepayment->installment_amount;
            }
        }

        // Calculate current installment number
        $paidCount = LoanRepayments::where('loan_id', $loan->id)
            ->where('status', 'paid')
            ->count();
        $currentInstallmentNo = $paidCount + 1;

        $loanInfo = "Total Installments: {$loan->total_installments} | Remaining: {$loan->remaining_installments} | Current Installment: #{$currentInstallmentNo}";

        return [
            'ded_loan_amount' => $emiAmount,
            'loan_balance' => $loan->balance_amount ?? 0,
            'loan_id' => $loan->id,
            'total_installments' => $loan->total_installments,
            'remaining_installments' => $loan->remaining_installments,
            'current_installment_no' => $currentInstallmentNo,
            'loan_info' => $loanInfo
        ];
    }
    /**
     * Get applicable salary details for an employee, dynamically applying any scheduled increment.
     */
    private function getApplicableSalaryDetail($companyId, $employeeId, $year, $month)
    {
        $salaryDetail = EmployeeWiseSalaryDetail::where('company_id', $companyId)
            ->where('employee_id', $employeeId)
            ->first();

        if (!$salaryDetail) {
            return null;
        }

        // Fetch all active increments for this employee
        $increments = \App\Models\EmployeeIncrementDetails::where('company_id', $companyId)
            ->where('employee_id', $employeeId)
            ->where('status', 'active')
            ->get();

        $monthMap = [
            'january' => 1,
            'february' => 2,
            'march' => 3,
            'april' => 4,
            'may' => 5,
            'june' => 6,
            'july' => 7,
            'august' => 8,
            'september' => 9,
            'october' => 10,
            'november' => 11,
            'december' => 12,
            'jan' => 1,
            'feb' => 2,
            'mar' => 3,
            'apr' => 4,
            'jun' => 6,
            'jul' => 7,
            'aug' => 8,
            'sep' => 9,
            'oct' => 10,
            'nov' => 11,
            'dec' => 12
        ];

        // Find the latest increment applicable for the calculation period ($year, $month)
        $latestIncrement = $increments->filter(function ($inc) use ($year, $month, $monthMap) {
            $incYear = (int) $inc->effective_year;
            $incMonthName = strtolower(trim($inc->effective_month ?? ''));
            $incMonth = $monthMap[$incMonthName] ?? 1;

            return $incYear < $year || ($incYear == $year && $incMonth <= $month);
        })->sortByDesc(function ($inc) use ($monthMap) {
            $incYear = (int) $inc->effective_year;
            $incMonthName = strtolower(trim($inc->effective_month ?? ''));
            $incMonth = $monthMap[$incMonthName] ?? 1;
            return ($incYear * 100) + $incMonth;
        })->first();

        if ($latestIncrement) {
            $salaryDetail->basic_da = $latestIncrement->basic_da;
            $salaryDetail->hra = $latestIncrement->hra;
            $salaryDetail->conveyance_allowance = $latestIncrement->conveyance_allowance;
            $salaryDetail->medical_allowance = $latestIncrement->medical_allowance;
            $salaryDetail->special_allowance = $latestIncrement->special_allowance;

            $basic = (float) ($latestIncrement->basic_da ?? 0);
            $hra = (float) ($latestIncrement->hra ?? 0);
            $conveyance = (float) ($latestIncrement->conveyance_allowance ?? 0);
            $medical = (float) ($latestIncrement->medical_allowance ?? 0);
            $special = (float) ($latestIncrement->special_allowance ?? 0);
            $salaryDetail->ctc = round($basic + $hra + $conveyance + $medical + $special, 2);

            if (isset($latestIncrement->per_day_salary) && $latestIncrement->per_day_salary > 0) {
                $salaryDetail->per_day_salary = $latestIncrement->per_day_salary;
            }
            if (isset($latestIncrement->per_hour_salary) && $latestIncrement->per_hour_salary > 0) {
                $salaryDetail->per_hour_salary = $latestIncrement->per_hour_salary;
            }
        }

        return $salaryDetail;
    }

    /**
     * Calculate all salary components
     */
    private function calculateSalaryComponents($salaryDetail, $attendanceData, $holidayCount, $leaveData, $bonusData, $loanData, $totalDaysInMonth, $perDaySalary, $salaryDivisor = 30, $employee = null, $startDate = null, $endDate = null, $operationEarnings = 0)
    {
        $ctc = $salaryDetail->ctc ?? 0;
        $basicDa = $salaryDetail->basic_da ?? 0;
        $hra = $salaryDetail->hra ?? 0;
        $conveyanceAllowance = $salaryDetail->conveyance_allowance ?? 0;
        $medicalAllowance = $salaryDetail->medical_allowance ?? 0;
        $specialAllowance = $salaryDetail->special_allowance ?? 0;

        // Ensure divisor is not zero
        if ($salaryDivisor <= 0)
            $salaryDivisor = 30;

        $monthCountType = $salaryDetail->salary_calculation_month_count ? trim($salaryDetail->salary_calculation_month_count) : 'Fix 30 Days';
        $monthCountTypeNormalized = strtolower($monthCountType);
        $includeWeekOff = !str_contains($monthCountTypeNormalized, 'week off');

        // Calculate days (same logic as Attendance Report: present + half*0.5 + week_off_working + week_off + holiday + company leave)
        $weekOffWorkingDays = $attendanceData['week_off_working_days'] ?? 0;
        // USER REQUEST: Include week off working days in Calculate Days (Payable) so the employee gets paid for it.
        $calculateDays = $attendanceData['total_present_day'] + ($attendanceData['half_day'] * 0.5) +
            ($includeWeekOff ? $attendanceData['total_week_off'] : 0) +
            ($attendanceData['total_holiday_days'] ?? 0) + $leaveData['total_company_pay_leave'] +
            $weekOffWorkingDays;

        // Fix 30 Days Logic (Hybrid: If Full Month, use 30 - Deductions. If Partial, use Sum)
        if (str_contains($monthCountTypeNormalized, 'fix 30') && $employee && $startDate && $endDate) {
            $joiningDate = $employee->joining_date ? Carbon::parse($employee->joining_date) : null;
            $resignationDate = $employee->resign_date ? Carbon::parse($employee->resign_date) : null;

            $start = Carbon::parse($startDate);
            $end = Carbon::parse($endDate);

            $isPartial = false;
            // Check Joining
            if ($joiningDate && $joiningDate->gt($start)) {
                $isPartial = true;
            }
            // Check Resignation
            if ($resignationDate && $resignationDate->lt($end)) {
                $isPartial = true;
            }

            // Fix for Current Running Month (Future End Date)
            // Check if the period end date is in the future OR if it's the current active month.
            // This forces "Actual Days" calculation logic.
            $today = \Carbon\Carbon::now()->startOfDay();
            if ($end->gt($today) || $end->isCurrentMonth()) {
                $isPartial = true;
            }

            if (!$isPartial) {
                // Full Month -> Base 30 - Absents - Unpaid Leaves
                // Note: Sandwich Leave is typically Unpaid or Deducted.
                // total_absent from getAttendanceData includes days marked as 'ABSENT'.
                // sandwich_leave_count is separate.

                $unpaidHalfDays = max(0, $attendanceData['half_day'] - (($leaveData['total_company_pay_leave'] ?? 0) * 2));
                $deductions = $attendanceData['total_absent'] +
                    ($unpaidHalfDays * 0.5) +
                    $leaveData['total_employee_pay_leave'] + // Assuming employee pay leave is unpaid
                    $attendanceData['sandwich_leave_count'];

                // For 'Fix 30', we disregard the Summation > 30.
                $calculateDays = 30 - $deductions;

                // Safety: Don't go below 0
                if ($calculateDays < 0)
                    $calculateDays = 0;
            }
        }


        // Determine Salary Classification Mode
        $salaryClassification = strtolower(trim($salaryDetail->salary_classification ?? ''));
        $isHourlyMode = ($salaryClassification === 'per hours salary');

        // Initial Rate logic:
        // By default, $perDaySalary and $salaryDivisor calculate a DAILY rate.
        // If Hourly Mode:
        // We need an HOURLY Rate.
        // Option A: Rate = CTC / (DivisorDays * 8 hours)
        // Option B: Rate = (CTC / DivisorDays) / 8 hours
        // Usually, Per Hour Salary means they are paid for HOURS WORKED.
        // So 'PayableUnits' becomes 'Worked Hours' instead of 'Worked Days'.

        $payableUnits = $calculateDays;
        $rateDivisor = $salaryDivisor; // Default Day Divisor

        if ($isHourlyMode) {
            // Use Total Worked HOURS as the multiplier unit
            $payableUnits = $attendanceData['total_working_hours_decimal'] ?? 0;

            // Adjust Rate to be Hourly
            // For Components (Basic, HRA, etc.), they are monthly figures.
            // ComponentHourlyRate = (ComponentMonthly / DivisorDays) / 8 [assuming 8 hour shift default or standard].
            // TODO: Ideally check shift hours from attendance, but for a global rate, 8 is standard or we need a config.
            // Using 8 for now as standard conversion.
            // Updated logic: We will adjust the logic below to use ($amount / $salaryDivisor / 8) * $payableUnits(Hours)
        }

        // Calculate amounts based on per day salary
        $presentDayAmount = $attendanceData['total_present_day'] * $perDaySalary;

        // USER REQUEST: Calculate 1 day's salary for working on Week Off.
        $workingWeekoffAmount = $weekOffWorkingDays * $perDaySalary;

        // Fixed: Zero out Week Off Amount if Week Offs are excluded
        $employeeWeekoffAmount = $includeWeekOff ? ($attendanceData['total_week_off'] * $perDaySalary) : 0;

        $companyPayLeaveAmount = $leaveData['total_company_pay_leave'] * $perDaySalary;
        $employeePayLeaveAmount = $leaveData['total_employee_pay_leave'] * $perDaySalary;

        // Fixed Salary Structure (FXS)
        $fxsBasic = $basicDa;
        $fxsHra = $hra;
        $fxsOther = $conveyanceAllowance + $medicalAllowance + $specialAllowance;
        $fxsTotalEarning = $fxsBasic + $fxsHra + $fxsOther;

        // Daily Wage Salary (DWS) - Pro-rata based on working days
        // Fixed BUG: Use dynamic salaryDivisor instead of hardcoded 30

        // $payableDays is now handled by $payableUnits variable which toggles between Days and Hours

        // Calculate factor based on Mode
        // If Hourly: Factor = (Amount / Divisor / 8)
        // If Daily: Factor = (Amount / Divisor)
        $hourlyDivisor = ($isHourlyMode) ? 8 : 1;

        $dwsBasic = ($basicDa / $salaryDivisor / $hourlyDivisor) * $payableUnits;
        $dwsHra = ($hra / $salaryDivisor / $hourlyDivisor) * $payableUnits;
        $dwsDa = 0; // DA included in basic
        $dwsOther = (($conveyanceAllowance + $medicalAllowance + $specialAllowance) / $salaryDivisor / $hourlyDivisor) * $payableUnits;

        // Fixed BUG: Missing HRA in total
        $dwsTotalEarning = $dwsBasic + $dwsHra + $dwsDa + $dwsOther;

        // OT Calculation
        $perHourSalary = $perDaySalary / 8;
        $earnOtPayableAmt = $attendanceData['earn_ot_hours'] * $perHourSalary;

        // Earn Sub Total
        $earnSubTotal = $earnOtPayableAmt + $bonusData['bonus_amount'];

        // Shortfall Deduction
        $shortfallAmount = 0;
        if (isset($attendanceData['total_shortfall_minutes']) && $attendanceData['total_shortfall_minutes'] > 0) {
            // Calculate per minute salary (using standard 8 hours = 480 mins)
            // Or use dynamic? Standard is safer for deduction.
            $perMinuteSalary = $perDaySalary / 480;
            $shortfallAmount = $attendanceData['total_shortfall_minutes'] * $perMinuteSalary;
        }

        // Total Earning with Shortfall Deduction (Note: working weekoff amount is already included in dwsTotalEarning)
        $totalEarning = $dwsTotalEarning + $earnSubTotal + (float) $operationEarnings;
        // $totalEarning = ($dwsTotalEarning - $shortfallAmount) + $earnSubTotal + (float) $operationEarnings;

        // Deductions
        // Deductions
        // PF Calculation (12% of Basic + DA if applicable)
        // PF Calculation (12% of Total Earned Salary - "CTC Amount")
        // Fixed: User requested PF on CTC (Earned Total), not just Basic.
        $pfBase = $dwsTotalEarning;
        $dedEmployeePf = 0;
        $dedPradhanMantriPf = 0;

        if (in_array($salaryDetail->pf, ['yes', '1', 1, true], true)) {
            $pfPercentage = $salaryDetail->pf_percentage ?? 12;
            $calculatedPf = ($pfBase * $pfPercentage) / 100;
            // Cap PF at 1800 if it exceeds
            $dedEmployeePf = ($calculatedPf > 1800) ? 1800 : $calculatedPf;
        }

        if (in_array($salaryDetail->pradhanmantri_pf, ['yes', '1', 1, true], true)) {
            $pmPfPercentage = $salaryDetail->pradhanmantri_pf_percentage ?? 0;
            $dedPradhanMantriPf = ($pfBase * $pmPfPercentage) / 100;
        }

        // ESI Calculation
        $dedEsiEmployee = 0;
        $dedEsiCompany = 0;

        if (in_array($salaryDetail->esi_employee_side, ['yes', '1', 1, true], true)) {
            $esiEmployeePercentage = $salaryDetail->esi_employee_side_percentage ?? 0.75;
            $dedEsiEmployee = ($totalEarning * $esiEmployeePercentage) / 100;
        }

        if (in_array($salaryDetail->is_esi_company_side, ['yes', '1', 1, true], true)) {
            $esiCompanyPercentage = $salaryDetail->esi_company_side_percentage ?? 3.25;
            $dedEsiCompany = ($totalEarning * $esiCompanyPercentage) / 100;
        }

        // PT (Professional Tax)
        $dedPt = 0;
        // Accept 'yes', '1', 1, or true
        if (in_array($salaryDetail->pt, ['yes', '1', 1, true], true) || $salaryDetail->pt == 'yes') {
            $dedPt = $salaryDetail->pt_amount ?? 0;
        }

        // Insurance
        $dedInsurance = 0;
        if (in_array($salaryDetail->insurance, ['yes', '1', 1, true], true)) {
            $dedInsurance = $salaryDetail->insurance_amount ?? 0;
        }

        // TDS
        $dedTds = 0;
        if (in_array($salaryDetail->tds, ['yes', '1', 1, true], true)) {
            $tdsPercentage = $salaryDetail->tds_percentage ?? 0;
            $dedTds = ($ctc * $tdsPercentage) / 100;
        }

        // Welfare Fund
        $dedWf = 0;
        if (in_array($salaryDetail->is_welfare_fund_applied, ['yes', '1', 1, true], true)) {
            $dedWf = $salaryDetail->welfare_fund_amount ?? 0;
        }

        // Total Deduction
        $totalDeduction = $dedEmployeePf + $dedPradhanMantriPf + $dedEsiEmployee +
            $dedPt + $dedInsurance + $dedTds + $dedWf + $loanData['ded_loan_amount'];

        // Net Bank Pay
        $netBankPay = $totalEarning - $totalDeduction;

        // Rounding (Nearest Rupee) for summary fields
        // Keep component-level precision as-is, but round totals so UI/print shows .00
        $totalEarningRounded = round($totalEarning, 0);
        $totalDeductionRounded = round($totalDeduction, 0);
        $netBankPayRounded = $totalEarningRounded - $totalDeductionRounded;

        return [
            // Days calculation
            'calculate_days' => round($calculateDays, 2),
            'total_present_day' => $attendanceData['total_present_day'],
            'half_day' => $attendanceData['half_day'],
            'working_week_off' => $attendanceData['week_off_working_days'] ?? 0,
            'week_off_working_days' => $attendanceData['week_off_working_days'] ?? 0,
            'holiday' => $holidayCount,
            'total_week_off' => $attendanceData['total_week_off'],
            'total_sandwich_leave' => $attendanceData['sandwich_leave_count'] ?? 0,
            'total_leave' => $leaveData['total_leave'],
            'total_company_pay_leave' => $leaveData['total_company_pay_leave'],
            'total_employee_pay_leave' => $leaveData['total_employee_pay_leave'],
            'total_absent' => $attendanceData['total_absent'] + ($attendanceData['sandwich_leave_count'] ?? 0),
            'total_day' => $totalDaysInMonth,
            'working_hour' => $attendanceData['working_hour'],

            // Salary details
            'ctc' => round($ctc, 2),
            'salary_calculation_month_count' => $monthCountType,
            'per_day_salary' => round($perDaySalary, 2),
            'conveyance_allowance' => round($conveyanceAllowance, 2),
            'medical_allowance' => round($medicalAllowance, 2),
            'special_allowance' => round($specialAllowance, 2),

            // Amount calculations
            'present_day_amount' => round($presentDayAmount, 2),
            'employee_weekoff_amount' => round($employeeWeekoffAmount, 2),
            'working_weekoff_amount' => round($workingWeekoffAmount, 2),
            'company_pay_leave_amount' => round($companyPayLeaveAmount, 2),
            'employee_pay_leave_amount' => round($employeePayLeaveAmount, 2),

            // FXS (Fixed Salary Structure)
            'fxs_basic' => round($fxsBasic, 2),
            'fxs_hra' => round($fxsHra, 2),
            'fxs_other' => round($fxsOther, 2),
            'fxs_total_earning' => round($fxsTotalEarning, 2),

            // DWS (Daily Wage Salary)
            'dws_basic' => round($dwsBasic, 2),
            'dws_da' => round($dwsDa, 2),
            'dws_other' => round($dwsOther, 2),
            'dws_total_earning' => round($dwsTotalEarning, 2),

            // OT
            'actual_total_ot_hours' => round($attendanceData['actual_total_ot_hours'], 2),
            'earn_ot_hours' => round($attendanceData['earn_ot_hours'], 2),
            'earn_ot_payable_amt' => round($earnOtPayableAmt, 2),
            'earn_ot_days' => $attendanceData['earn_ot_days'],
            'shortfall_amount' => round($shortfallAmount, 2),

            // Bonus
            'bonus_amount' => round($bonusData['bonus_amount'], 2),
            'bonus_amount_adjustment' => 0,

            // Earnings
            'earn_sub_total' => round($earnSubTotal, 2),
            'total_earning' => $totalEarningRounded,

            // Deductions
            'ded_employee_pf' => round($dedEmployeePf, 2),
            'ded_pradhan_mantri_pf' => round($dedPradhanMantriPf, 2),
            'ded_esi_employee' => round($dedEsiEmployee, 2),
            'ded_esi_company' => round($dedEsiCompany, 2),
            'ded_pt' => round($dedPt, 2),
            'ded_insurance' => round($dedInsurance, 2),
            'ded_tds' => round($dedTds, 2),
            'tds_amount_adjustment' => 0,
            'ded_wf' => round($dedWf, 2),
            'ded_loan_amount' => round($loanData['ded_loan_amount'], 2),
            'loan_amount_adjustment' => 0,
            'loan_id' => $loanData['loan_id'] ?? null,
            'total_installments' => $loanData['total_installments'] ?? 0,
            'remaining_installments' => $loanData['remaining_installments'] ?? 0,
            'current_installment_no' => $loanData['current_installment_no'] ?? 0,
            'loan_info' => $loanData['loan_info'] ?? 'No active loan',
            'ded_other' => 0,
            'total_deduction' => $totalDeductionRounded,

            // Net Pay
            'net_bank_pay' => $netBankPayRounded,
            'given_calculate_salary' => round($operationEarnings, 2),

            // PF/ESI rules for client-side/re-calculations
            'pf_enabled' => in_array($salaryDetail->pf, ['yes', '1', 1, true], true),
            'pf_percentage' => (float) ($salaryDetail->pf_percentage ?? 12),
            'pradhanmantri_pf_enabled' => in_array($salaryDetail->pradhanmantri_pf, ['yes', '1', 1, true], true),
            'pradhanmantri_pf_percentage' => (float) ($salaryDetail->pradhanmantri_pf_percentage ?? 0),
            'esi_employee_enabled' => in_array($salaryDetail->esi_employee_side, ['yes', '1', 1, true], true),
            'esi_employee_percentage' => (float) ($salaryDetail->esi_employee_side_percentage ?? 0.75),
            'esi_company_enabled' => in_array($salaryDetail->is_esi_company_side, ['yes', '1', 1, true], true),
            'esi_company_percentage' => (float) ($salaryDetail->esi_company_side_percentage ?? 3.25),
        ];
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $modules = $this->modules;
        $modules['authLoginUserDetail'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails : null;
        $modules['company_id'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails?->company_id : null;
        $loginUserId = ($modules['authLoginUserDetail'] && $modules['authLoginUserDetail']?->id) ? $modules['authLoginUserDetail']?->id : null;

        if (count(config('constants.permissions'))) {
            foreach (config('constants.permissions') as $key => $value) {
                $modules[$value . '_permission'] = (isset($modules['company_id']) && !$modules['company_id']) ? true : Gate::check('hasPermission', [$value, $modules['module_name']]);
            }
        }

        if (!$modules['add_permission']) {
            if (isset($request) && $request->ajax()) {
                return $this->sendError('Unauthorized', [], [], 403);
            }
            abort(403, 'Unauthorized');
        }

        $validator = Validator::make($request->all(), [
            'company_id' => ['required', 'exists:companies,id'],
            'branch_id' => ['required', 'exists:branches,id'],
            'department_id' => ['required', 'exists:departments,id'],
            'employee_id' => ['required', 'exists:employees,id'],
            'year' => ['required', 'integer'],
            'month' => ['required', 'integer'],
        ]);

        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator)->withInput();
        }

        try {
            // Security: Enforce company_id for restricted users
            if ($this->authenticateLoginUserDetails && $this->authenticateLoginUserDetails->company_id) {
                $request->merge(['company_id' => $this->authenticateLoginUserDetails->company_id]);
            }

            $companyId = $request->company_id;
            $branchId = $request->branch_id;
            $departmentId = $request->department_id;
            $employeeId = $request->employee_id;
            $year = (int) $request->year;
            $month = (int) $request->month;

            // Check if salary already exists for this period
            $existingSalary = Salary::where('company_id', $companyId)
                ->where('employee_id', $employeeId)
                ->where('year', $year)
                ->where('month', $month)
                ->first();

            if ($existingSalary && $existingSalary->is_locked) {
                return Redirect::back()->withErrors('Salary is locked and cannot be recalculated. Please unlock it first.')->withInput();
            }

            // Get employee details
            $employee = Employee::with(['branch', 'employmentDetail'])->find($employeeId);
            if (!$employee) {
                return Redirect::back()->withErrors('Employee not found.')->withInput();
            }

            // Block calculation if resigned before the calculation month
            if ($employee->status === 'resigned' || !empty($employee->resign_date)) {
                $resignDate = !empty($employee->resign_date) ? Carbon::parse($employee->resign_date)->endOfDay() : null;
                $calculationStartDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
                if ($resignDate && $calculationStartDate->gt($resignDate)) {
                    return Redirect::back()->withErrors('Salary calculation stopped. Employee resigned before this month.')->withInput();
                }
            }

            // Get salary details for employee (dynamically applying any active increment for the month/year)
            $salaryDetail = $this->getApplicableSalaryDetail($companyId, $employeeId, $year, $month);

            if (!$salaryDetail) {
                return Redirect::back()->withErrors('Salary details not found for this employee. Please configure salary details first.')->withInput();
            }

            // Calculate date range for the month
            $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
            $totalDaysInMonth = $startDate->daysInMonth;

            // Capture snapshots before calculation
            $snapshots = $this->captureDataSnapshots($companyId, $employeeId, $startDate, $endDate, $salaryDetail);

            // Get holiday dates & leave dates (For Exclusion Logic)
            $holidayDates = $this->getHolidayDates($companyId, $startDate, $endDate);
            $leaveDates = $this->getLeaveDates($companyId, $employeeId, $startDate, $endDate);

            // Get attendance data
            $attendanceData = $this->getAttendanceData($companyId, $employeeId, $startDate, $endDate, $salaryDetail, $holidayDates, $leaveDates);

            // Get holiday count
            $holidayCount = $this->getHolidayCount($companyId, $startDate, $endDate);

            // Get leave data
            $leaveData = $this->getLeaveData($companyId, $employeeId, $startDate, $endDate);

            // Get bonus data
            $bonusData = $this->getBonusData($companyId, $employeeId, $branchId, $year, $month);

            // Get loan EMI data
            $loanData = $this->getLoanData($companyId, $employeeId);

            // Calculate per day salary
            $ctc = $salaryDetail->ctc ?? 0;
            // Determine divisor based on configuration
            $monthCountType = trim($salaryDetail->salary_calculation_month_count ?? 'Fix 30 Days');
            $salaryDivisor = 30; // Default

            if ($monthCountType === 'Per Month Total Days') {
                $salaryDivisor = $totalDaysInMonth;
            } elseif ($monthCountType === 'Per Month Total Days - Week Off') {
                // Calculate total week offs in the entire month
                $totalWeekOffs = 0;
                $weekOffDays = [];
                if ($salaryDetail && $salaryDetail->week_off) {
                    $weekOffArray = json_decode($salaryDetail->week_off, true);
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
                    if (is_array($weekOffArray)) {
                        foreach ($weekOffArray as $wo) {
                            $woStr = strtolower(trim($wo));
                            if (isset($dayMap[$woStr])) {
                                $weekOffDays[] = $dayMap[$woStr];
                            }
                        }
                    }
                }

                for ($d = 1; $d <= $totalDaysInMonth; $d++) {
                    $currentDay = Carbon::createFromDate($year, $month, $d);
                    if (in_array($currentDay->dayOfWeek, $weekOffDays)) {
                        $totalWeekOffs++;
                    }
                }
                $salaryDivisor = $totalDaysInMonth - $totalWeekOffs;
            }

            // Avoid division by zero
            if ($salaryDivisor <= 0)
                $salaryDivisor = 30;

            $perDaySalary = $ctc / $salaryDivisor;

            // Get operation earnings total
            $operationEarnings = OperationsRateList::where('company_id', $companyId)
                ->where('employee_id', $employeeId)
                ->where('month', $month)
                ->where('year', $year)
                ->sum('total_amount');

            // Calculate salary components
            $calculatedData = $this->calculateSalaryComponents(
                $salaryDetail,
                $attendanceData,
                $holidayCount,
                $leaveData,
                $bonusData,
                $loanData,
                $totalDaysInMonth,
                $perDaySalary,
                $salaryDivisor,
                $employee,
                $startDate,
                $endDate,
                $operationEarnings
            );

            // HANDLE MANUAL ADJUSTMENTS
            // These fields are not calculated but come from the form input. 
            // We need to merge them and recalculate totals.

            // 1. Manual Inputs
            $manualAdjustments = [
                'adjustment_days' => $request->input('adjustment_days', 0),
                'adjustment_remark' => $request->input('adjustment_remark'),
                'earn_performation_incentive' => $request->input('earn_performation_incentive', 0),
                'bonus_amount_adjustment' => $request->input('bonus_amount_adjustment', 0),
                'tds_amount_adjustment' => $request->input('tds_amount_adjustment', 0),
                'loan_amount_adjustment' => $request->input('loan_amount_adjustment', 0),
                'ded_other' => $request->input('ded_other', 0),
                'ded_other_remark' => $request->input('ded_other_remark'),
                'ded_advance' => $request->input('ded_advance', 0),
            ];

            // 2. Add to Calculated Data
            foreach ($manualAdjustments as $key => $value) {
                // Ensure numeric values are floats
                if (!in_array($key, ['adjustment_remark', 'ded_other_remark'])) {
                    $calculatedData[$key] = (float) $value;
                } else {
                    $calculatedData[$key] = $value;
                }
            }

            // Adjust by adjustment_days
            $adjustmentDays = (float) $calculatedData['adjustment_days'];
            $perDaySalary = (float) $calculatedData['per_day_salary'];
            $adjustmentAmount = $adjustmentDays * $perDaySalary;

            $calculatedData['calculate_days'] += $adjustmentDays;
            $calculatedData['dws_total_earning'] += $adjustmentAmount;

            // 3. Recalculate Totals
            // Recalculate PF based on updated dws_total_earning
            $pfBase = (float) $calculatedData['dws_total_earning'];
            if (in_array($salaryDetail->pf, ['yes', '1', 1, true], true)) {
                $pfPercentage = $salaryDetail->pf_percentage ?? 12;
                $calculatedPf = ($pfBase * $pfPercentage) / 100;
                $calculatedData['ded_employee_pf'] = ($calculatedPf > 1800) ? 1800 : $calculatedPf;
            } else {
                $calculatedData['ded_employee_pf'] = 0;
            }

            if (in_array($salaryDetail->pradhanmantri_pf, ['yes', '1', 1, true], true)) {
                $pmPfPercentage = $salaryDetail->pradhanmantri_pf_percentage ?? 0;
                $calculatedData['ded_pradhan_mantri_pf'] = ($pfBase * $pmPfPercentage) / 100;
            } else {
                $calculatedData['ded_pradhan_mantri_pf'] = 0;
            }

            // Recalculate Earnings before ESI, since ESI is calculated on total_earning
            $calculatedData['earn_sub_total'] += $calculatedData['earn_performation_incentive'] + $calculatedData['bonus_amount_adjustment'];
            $calculatedData['total_earning'] += $calculatedData['earn_performation_incentive'] + $calculatedData['bonus_amount_adjustment'] + $adjustmentAmount;

            // Recalculate ESI
            if (in_array($salaryDetail->esi_employee_side, ['yes', '1', 1, true], true)) {
                $esiEmployeePercentage = $salaryDetail->esi_employee_side_percentage ?? 0.75;
                $calculatedData['ded_esi_employee'] = ($calculatedData['total_earning'] * $esiEmployeePercentage) / 100;
            } else {
                $calculatedData['ded_esi_employee'] = 0;
            }

            if (in_array($salaryDetail->is_esi_company_side, ['yes', '1', 1, true], true)) {
                $esiCompanyPercentage = $salaryDetail->esi_company_side_percentage ?? 3.25;
                $calculatedData['ded_esi_company'] = ($calculatedData['total_earning'] * $esiCompanyPercentage) / 100;
            } else {
                $calculatedData['ded_esi_company'] = 0;
            }

            // Round values
            $calculatedData['ded_employee_pf'] = round($calculatedData['ded_employee_pf'], 2);
            $calculatedData['ded_pradhan_mantri_pf'] = round($calculatedData['ded_pradhan_mantri_pf'], 2);
            $calculatedData['ded_esi_employee'] = round($calculatedData['ded_esi_employee'], 2);
            $calculatedData['ded_esi_company'] = round($calculatedData['ded_esi_company'], 2);

            // Deductions
            $calculatedData['total_deduction'] = $calculatedData['ded_employee_pf'] +
                $calculatedData['ded_pradhan_mantri_pf'] +
                $calculatedData['ded_esi_employee'] +
                $calculatedData['ded_pt'] +
                $calculatedData['ded_insurance'] +
                $calculatedData['ded_tds'] +
                $calculatedData['tds_amount_adjustment'] +
                $calculatedData['ded_wf'] +
                $calculatedData['ded_loan_amount'] +
                $calculatedData['loan_amount_adjustment'] +
                $calculatedData['ded_advance'] +
                $calculatedData['ded_other'];

            // Rounding (Nearest Rupee) for summary fields
            $calculatedData['total_earning'] = round((float) ($calculatedData['total_earning'] ?? 0), 0);
            $calculatedData['total_deduction'] = round((float) ($calculatedData['total_deduction'] ?? 0), 0);

            // Net Pay (based on rounded totals)
            $calculatedData['net_bank_pay'] = $calculatedData['total_earning'] - $calculatedData['total_deduction'];


            // Prepare salary data
            $salaryData = array_merge($calculatedData, [
                'company_id' => $companyId,
                'branch_id' => $branchId ?? $employee->branch_id,
                'department_id' => $departmentId ?? $employee->employmentDetail?->department_id,
                'employee_id' => $employeeId,
                'year' => $year,
                'month' => $month,
                'added_date' => now()->format('Y-m-d'),
                'created_by' => $loginUserId,
                'status' => 'active',
                // Add snapshot data
                'calculation_date' => now(),
                'attendance_snapshot_date' => $snapshots['attendance_snapshot_date'],
                'attendance_records_count' => $snapshots['attendance_records_count'],
                'leave_snapshot_date' => $snapshots['leave_snapshot_date'],
                'leave_records_count' => $snapshots['leave_records_count'],
                'holiday_snapshot_date' => $snapshots['holiday_snapshot_date'],
                'holiday_records_count' => $snapshots['holiday_records_count'],
                'salary_detail_snapshot_date' => $snapshots['salary_detail_snapshot_date'],
                'data_modified' => false,
                'data_modification_details' => null,
            ]);

            // Update or create salary record
            if ($existingSalary) {
                $existingSalary->update($salaryData);
                $salary = $existingSalary;
            } else {
                $salary = Salary::create($salaryData);
            }

            $months = [
                1 => 'Jan',
                2 => 'Feb',
                3 => 'Mar',
                4 => 'Apr',
                5 => 'May',
                6 => 'Jun',
                7 => 'Jul',
                8 => 'Aug',
                9 => 'Sep',
                10 => 'Oct',
                11 => 'Nov',
                12 => 'Dec'
            ];
            $monthName = $months[$salary->month] ?? $salary->month;
            $description = ($employee->employee_code ?? '-') . ' - ' . ($employee->full_name ?? '-') . ' Salary Net Pay (' . $monthName . ' ' . $salary->year . ')';
            $ledgerRow = [
                'company_id' => $companyId,
                'employee_id' => $employeeId,
                'entry_date' => Carbon::parse($salary->calculation_date ?? now())->format('Y-m-d'),
                'receipt_id' => 'salary-' . $salary->id,
                'payment_mode' => 'credit',
                'amount' => $salary->net_bank_pay ?? 0,
                'debit_amount' => 0,
                'credit_amount' => $salary->net_bank_pay ?? 0,
                'closing_balance' => 0,
                'description' => $description,
                'updated_at' => now(),
            ];
            // Ensure types
            $ledgerRow['company_id'] = (string) ($ledgerRow['company_id'] ?? '');
            $ledgerRow['employee_id'] = (string) ($ledgerRow['employee_id'] ?? '');
            $ledgerRow['receipt_id'] = (string) ($ledgerRow['receipt_id'] ?? '');
            $ledgerRow['amount'] = floatval($ledgerRow['amount'] ?? 0);
            $ledgerRow['debit_amount'] = floatval($ledgerRow['debit_amount'] ?? 0);
            $ledgerRow['credit_amount'] = floatval($ledgerRow['credit_amount'] ?? 0);

            // Prefer updating any existing salary ledger for the same company, employee and month
            try {
                $dt = Carbon::parse($ledgerRow['entry_date']);
                $year = $dt->year;
                $month = $dt->month;

                $existing = DB::table('account_ledgers')
                    ->where('company_id', $ledgerRow['company_id'])
                    ->where('employee_id', $ledgerRow['employee_id'])
                    ->whereRaw('YEAR(entry_date) = ? AND MONTH(entry_date) = ?', [$year, $month])
                    ->where(function ($q) use ($ledgerRow) {
                        $q->where('receipt_id', $ledgerRow['receipt_id'])
                            ->orWhere('description', 'like', '%Salary%');
                    })
                    ->first();

                if ($existing) {
                    DB::table('account_ledgers')->where('id', $existing->id)->update(array_merge($ledgerRow, ['updated_at' => now(), 'receipt_id' => $ledgerRow['receipt_id']]));
                } else {
                    DB::table('account_ledgers')->insert(array_merge($ledgerRow, ['created_at' => now(), 'updated_at' => now()]));
                }
            } catch (\Exception $ex) {
                Log::error('Salary ledger upsert failed: ' . $ex->getMessage());
                // fallback to updateOrInsert to avoid stopping the flow
                DB::table('account_ledgers')->updateOrInsert(
                    ['receipt_id' => $ledgerRow['receipt_id'], 'company_id' => $ledgerRow['company_id']],
                    array_merge($ledgerRow, ['created_at' => now(), 'updated_at' => now()])
                );
            }

            $this->pushSalaryToOldCRM($salary);

            return Redirect::route($modules['route'] . '.index')

                ->withSuccess($modules['title'] . ' calculated and saved successfully. Salary ID: ' . $salary->id);
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
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

        if (!$modules['update_permission']) {
            if (isset($request) && $request->ajax()) {
                return $this->sendError('Unauthorized', [], [], 403);
            }
            abort(403, 'Unauthorized');
        }

        try {
            View::share('modules', $modules);

            $salaryQuery = Salary::with(['company', 'branch', 'department', 'employee']);
            if (!empty($modules['company_id'])) {
                $salaryQuery->where('company_id', $modules['company_id']);
            }
            $edit = $salaryQuery->findOrFail($id);

            // Fix: Fetch salary_calculation_month_count from SalaryDetail as it's not in salaries table
            $salaryDetail = \App\Models\EmployeeWiseSalaryDetail::where('employee_id', $edit->employee_id)->first();
            $edit->salary_calculation_month_count = $salaryDetail?->salary_calculation_month_count ?? '';

            // Attach PF and ESI settings for client-side recalculation
            $edit->pf_enabled = $salaryDetail ? in_array($salaryDetail->pf, ['yes', '1', 1, true], true) : false;
            $edit->pf_percentage = $salaryDetail?->pf_percentage ?? 12;
            $edit->pradhanmantri_pf_enabled = $salaryDetail ? in_array($salaryDetail->pradhanmantri_pf, ['yes', '1', 1, true], true) : false;
            $edit->pradhanmantri_pf_percentage = $salaryDetail?->pradhanmantri_pf_percentage ?? 0;
            $edit->esi_employee_enabled = $salaryDetail ? in_array($salaryDetail->esi_employee_side, ['yes', '1', 1, true], true) : false;
            $edit->esi_employee_percentage = $salaryDetail?->esi_employee_side_percentage ?? 0.75;
            $edit->esi_company_enabled = $salaryDetail ? in_array($salaryDetail->is_esi_company_side, ['yes', '1', 1, true], true) : false;
            $edit->esi_company_percentage = $salaryDetail?->esi_company_side_percentage ?? 3.25;

            $loanData = $this->getLoanData($edit->company_id, $edit->employee_id);
            $edit->loan_info = $loanData['loan_info'] ?? 'No active loan';
            // return $edit;
            View::share('edit', $edit);

            return view($modules['folder_path'] . '.form');
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $modules = $this->modules;
        $modules['authLoginUserDetail'] = $this->authenticateLoginUserDetails ?? null;
        $modules['company_id'] = $modules['authLoginUserDetail']?->company_id ?? null;
        $loginUserId = $modules['authLoginUserDetail']?->id ?? null;

        if (count(config('constants.permissions'))) {
            foreach (config('constants.permissions') as $value) {
                $modules[$value . '_permission'] = (!$modules['company_id'])
                    ? true
                    : Gate::check('hasPermission', [$value, $modules['module_name']]);
            }
        }

        if (!$modules['update_permission']) {
            if ($request->ajax()) {
                return $this->sendError('Unauthorized', [], [], 403);
            }
            abort(403, 'Unauthorized');
        }

        try {
            $data = $request->all();

            // Rounding (Nearest Rupee) for summary fields
            if (array_key_exists('total_earning', $data)) {
                $data['total_earning'] = round((float) $data['total_earning'], 0);
            }
            if (array_key_exists('total_deduction', $data)) {
                $data['total_deduction'] = round((float) $data['total_deduction'], 0);
            }
            if (isset($data['total_earning']) && isset($data['total_deduction'])) {
                $data['net_bank_pay'] = (float) $data['total_earning'] - (float) $data['total_deduction'];
            } elseif (array_key_exists('net_bank_pay', $data)) {
                $data['net_bank_pay'] = round((float) $data['net_bank_pay'], 0);
            }

            // Convert Time Format (HH:MM) to Decimal for OT Hours
            if (isset($data['actual_total_ot_hours']) && str_contains($data['actual_total_ot_hours'], ':')) {
                $parts = explode(':', $data['actual_total_ot_hours']);
                $data['actual_total_ot_hours'] = ($parts[0] + ($parts[1] / 60));
            }
            if (isset($data['earn_ot_hours']) && str_contains($data['earn_ot_hours'], ':')) {
                $parts = explode(':', $data['earn_ot_hours']);
                $data['earn_ot_hours'] = ($parts[0] + ($parts[1] / 60));
            }

            $data['updated_by'] = $loginUserId;

            $salaryQuery = Salary::query();
            if (!empty($modules['company_id'])) {
                $salaryQuery->where('company_id', $modules['company_id']);
            }
            $updateData = $salaryQuery->findOrFail($id);
            unset($data['_token'], $data['_method'], $data['edit_id']);
            $updateData->update($data);
            $this->pushSalaryToOldCRM($updateData);

            return Redirect::route($modules['route'] . '.index')->withSuccess($modules['title'] . ' updated successfully');
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id, Request $request)
    {
        $modules = $this->modules;
        $modules['authLoginUserDetail'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails : null;
        $modules['company_id'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails?->company_id : null;
        $loginUserId = ($modules['authLoginUserDetail'] && $modules['authLoginUserDetail']?->id) ? $modules['authLoginUserDetail']?->id : null;

        if (count(config('constants.permissions'))) {
            foreach (config('constants.permissions') as $key => $value) {
                $modules[$value . '_permission'] = (isset($modules['company_id']) && !$modules['company_id']) ? true : Gate::check('hasPermission', [$value, $modules['module_name']]);
            }
        }

        if (!$modules['delete_permission']) {
            if (isset($request) && $request->ajax()) {
                return $this->sendError('Unauthorized', [], [], 403);
            }
            abort(403, 'Unauthorized');
        }

        $salaryQuery = Salary::query();
        if (!empty($modules['company_id'])) {
            $salaryQuery->where('company_id', $modules['company_id']);
        }
        $dataDelete = $salaryQuery->findOrFail($id);
        $isAjax = ($request->ajax()) ? true : false;

        try {
            if ($dataDelete) {
                $dataDelete->update(['deleted_by' => $loginUserId]);

                if ($dataDelete->delete()) {
                    if ($isAjax) {
                        return $this->sendResponse([], $modules['title'] . ' deleted successfully');
                    }
                    return true;
                }
            }
            if ($isAjax) {
                return $this->sendResponse([], "Something went wrong, please try again later");
            }
            return false;
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }

    public function restore($id)
    {
        $modules = $this->modules;
        $modules['authLoginUserDetail'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails : null;
        $modules['company_id'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails?->company_id : null;

        if (count(config('constants.permissions'))) {
            foreach (config('constants.permissions') as $key => $value) {
                $modules[$value . '_permission'] = (isset($modules['company_id']) && !$modules['company_id']) ? true : Gate::check('hasPermission', [$value, $modules['module_name']]);
            }
        }

        if (!$modules['restore_permission']) {
            if (isset($request) && $request->ajax()) {
                return $this->sendError('Unauthorized', [], [], 403);
            }
            abort(403, 'Unauthorized');
        }

        try {
            $salaryQuery = Salary::withTrashed();
            if (!empty($modules['company_id'])) {
                $salaryQuery->where('company_id', $modules['company_id']);
            }
            $restore_data = $salaryQuery->findOrFail($id);
            $restore_data->restore();

            return Redirect::back()->withSuccess($modules['title'] . ' restored successfully!');
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }

    public function status_update(Request $request)
    {
        $isAjax = ($request->ajax()) ? true : false;

        $modules = $this->modules;
        $modules['authLoginUserDetail'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails : null;
        $modules['company_id'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails?->company_id : null;

        if (count(config('constants.permissions'))) {
            foreach (config('constants.permissions') as $key => $value) {
                $modules[$value . '_permission'] = (isset($modules['company_id']) && !$modules['company_id']) ? true : Gate::check('hasPermission', [$value, $modules['module_name']]);
            }
        }

        if (!$modules['update_permission']) {
            if (isset($request) && $request->ajax()) {
                return $this->sendError('Unauthorized', [], [], 403);
            }
            abort(403, 'Unauthorized');
        }

        $validator = Validator::make($request->all(), [
            'id' => ['required', Rule::exists($modules['table_name'], 'id')],
            'update_status' => ['required', 'in:active,inactive']
        ]);

        if ($validator->fails() && $isAjax) {
            return $this->sendError($validator->messages()->first(), $validator->messages(), [], 401);
        }

        try {
            $salaryQuery = Salary::withTrashed();
            if (!empty($modules['company_id'])) {
                $salaryQuery->where('company_id', $modules['company_id']);
            }
            $salary = $salaryQuery->findOrFail($request?->id);
            if ($salary) {
                $salary->status = $request->update_status;
                $salary->save();
                if ($isAjax) {
                    return $this->sendResponse($salary, $modules['title'] . ' status updated successfully.');
                }
                return Redirect::route($modules['route'] . '.index')->withSuccess($modules['title'] . ' status updated successfully.');
            }
            if ($isAjax) {
                return $this->sendError('Something went wrong, please try again later');
            }
            return Redirect::back()->withErrors('Something went wrong, please try again later')->withInput();
        } catch (\Exception $e) {
            if ($isAjax) {
                return $this->sendError($e->getMessage(), $e->getMessage(), [], 401);
            }
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }

    public function print(Request $request)
    {
        $modules = $this->modules;
        $authUser = $this->authenticateLoginUserDetails;
        $modules['authLoginUserDetail'] = $authUser;
        $modules['company_id'] = $authUser?->company_id ?? null;
        $company_id = $modules['company_id'];
        $loginUserId = $authUser?->id ?? null;

        try {
            $moduleName = $modules['module_name'];
            $modules['viewPermission'] = Gate::check('hasPermission', ['view', $moduleName]);

            if ($request->ajax()) {
                if (!$modules['viewPermission']) {
                    return $this->sendError('Unauthorized', [], [], 403);
                }
            } elseif (!$modules['viewPermission']) {
                abort(403, 'Unauthorized');
            }

            $query = Salary::select('*')
                ->whereHas('employee', function ($q) {
                    $q->where('status', 'active');
                })
                ->where(function ($q) use ($modules, $loginUserId) {
                    if (Auth::guard('employees')->check() || !empty($modules['company_id'])) {
                        $companyId = $modules['company_id'] ?? Auth::guard('employees')->user()->company_id;
                        $q->where('company_id', $companyId);
                    }
                })
                ->with(['company', 'employee', 'branch', 'department'])
                ->orderBy('id', 'DESC');

            if ($request->filled('company')) {
                $query->where('company_id', $request->company);
            }
            if ($request->filled('branch')) {
                $query->where('branch_id', $request->branch);
            }
            if ($request->filled('employee')) {
                $query->where('employee_id', $request->employee);
            }
            if ($request->filled('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            $salaries = $query->get();

            return view($modules['folder_path'] . '.print', compact('salaries', 'company_id', 'modules'));
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }

    /**
     * Capture snapshots of source data at calculation time
     */
    private function captureDataSnapshots($companyId, $employeeId, $startDate, $endDate, $salaryDetail)
    {
        // Attendance snapshot
        $latestAttendance = Attendance::where('company_id', $companyId)
            ->where('employee_id', $employeeId)
            ->whereBetween('attendance_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->orderBy('updated_at', 'desc')
            ->first();

        $attendanceSnapshotDate = $latestAttendance ? $latestAttendance->updated_at : now();
        $attendanceCount = Attendance::where('company_id', $companyId)
            ->where('employee_id', $employeeId)
            ->whereBetween('attendance_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->count();

        // Leave snapshot
        $latestLeave = LeaveApplication::where('company_id', $companyId)
            ->where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('fromdate_time', [$startDate, $endDate])
                    ->orWhereBetween('todate_time', [$startDate, $endDate])
                    ->orWhere(fn($q2) => $q2->where('fromdate_time', '<', $startDate)->where('todate_time', '>', $endDate));
            })
            ->orderBy('updated_at', 'desc')
            ->first();

        $leaveSnapshotDate = $latestLeave ? $latestLeave->updated_at : now();
        $leaveCount = LeaveApplication::where('company_id', $companyId)
            ->where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('fromdate_time', [$startDate, $endDate])
                    ->orWhereBetween('todate_time', [$startDate, $endDate])
                    ->orWhere(fn($q2) => $q2->where('fromdate_time', '<', $startDate)->where('todate_time', '>', $endDate));
            })
            ->count();

        // Holiday snapshot
        $latestHoliday = Holiday::where('company_id', $companyId)
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('from_date', [$startDate, $endDate])
                    ->orWhereBetween('to_date', [$startDate, $endDate])
                    ->orWhere(fn($q2) => $q2->where('from_date', '<', $startDate)->where('to_date', '>', $endDate));
            })
            ->orderBy('updated_at', 'desc')
            ->first();

        $holidaySnapshotDate = $latestHoliday ? $latestHoliday->updated_at : now();
        $holidayCount = Holiday::where('company_id', $companyId)
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('from_date', [$startDate, $endDate])
                    ->orWhereBetween('to_date', [$startDate, $endDate])
                    ->orWhere(fn($q2) => $q2->where('from_date', '<', $startDate)->where('to_date', '>', $endDate));
            })
            ->count();

        // Salary detail snapshot
        $salaryDetailSnapshotDate = $salaryDetail->updated_at ?? now();

        return [
            'attendance_snapshot_date' => $attendanceSnapshotDate,
            'attendance_records_count' => $attendanceCount,
            'leave_snapshot_date' => $leaveSnapshotDate,
            'leave_records_count' => $leaveCount,
            'holiday_snapshot_date' => $holidaySnapshotDate,
            'holiday_records_count' => $holidayCount,
            'salary_detail_snapshot_date' => $salaryDetailSnapshotDate,
        ];
    }

    /**
     * Check data integrity for a salary record
     */
    public function checkDataIntegrity(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'salary_id' => ['required', 'exists:salaries,id'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->messages()->first()
            ], 422);
        }

        try {
            $salary = Salary::findOrFail($request->salary_id);

            // Security check
            if ($this->authenticateLoginUserDetails && $this->authenticateLoginUserDetails->company_id) {
                if ($salary->company_id != $this->authenticateLoginUserDetails->company_id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized access'
                    ], 403);
                }
            }

            $result = $salary->checkDataIntegrity();

            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error checking data integrity: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lock salary to prevent recalculation
     */
    public function lockSalary(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'salary_id' => ['required', 'exists:salaries,id'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->messages()->first()
            ], 422);
        }

        try {
            $salary = Salary::findOrFail($request->salary_id);

            // Security check
            if ($this->authenticateLoginUserDetails && $this->authenticateLoginUserDetails->company_id) {
                if ($salary->company_id != $this->authenticateLoginUserDetails->company_id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized access'
                    ], 403);
                }
            }

            $loginUserId = ($this->authenticateLoginUserDetails && $this->authenticateLoginUserDetails->id)
                ? $this->authenticateLoginUserDetails->id
                : null;

            $salary->lock($loginUserId);

            return response()->json([
                'success' => true,
                'message' => 'Salary locked successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error locking salary: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Unlock salary
     */
    public function unlockSalary(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'salary_id' => ['required', 'exists:salaries,id'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->messages()->first()
            ], 422);
        }

        try {
            $salary = Salary::findOrFail($request->salary_id);

            // Security check
            if ($this->authenticateLoginUserDetails && $this->authenticateLoginUserDetails->company_id) {
                if ($salary->company_id != $this->authenticateLoginUserDetails->company_id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized access'
                    ], 403);
                }
            }

            $salary->unlock();

            return response()->json([
                'success' => true,
                'message' => 'Salary unlocked successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error unlocking salary: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk calculate salary for all employees
     */
    public function bulkCalculateSalary(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => ['required', 'exists:companies,id'],
            'year' => ['required', 'integer', 'min:2020', 'max:2050'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'employee_id' => ['nullable', 'exists:employees,id'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->messages()->first(),
                'errors' => $validator->messages()
            ], 422);
        }

        try {
            $companyId = $request->company_id;
            // Security: Enforce company_id for restricted users
            if ($this->authenticateLoginUserDetails && $this->authenticateLoginUserDetails->company_id) {
                $companyId = $this->authenticateLoginUserDetails->company_id;
            }
            $branchId = $request->branch_id;
            $departmentId = $request->department_id;
            $employeeId = $request->employee_id;
            $year = (int) $request->year;
            $month = (int) $request->month;
            $loginUserId = ($this->authenticateLoginUserDetails && $this->authenticateLoginUserDetails->id)
                ? $this->authenticateLoginUserDetails->id
                : null;

            // Get all active employees with salary details configured
            $employeeQuery = Employee::where('company_id', $companyId)
                ->where('status', 'active')
                ->whereHas('salary_details', function ($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                })
                ->with(['branch', 'employmentDetail', 'salary_details']);

            // Filter by branch if provided
            if ($branchId) {
                $employeeQuery->where('branch_id', $branchId);
            }

            // Filter by department if provided
            if ($departmentId) {
                $employeeQuery->whereHas('employmentDetail', function ($q) use ($departmentId) {
                    $q->where('department_id', $departmentId);
                });
            }

            // Filter by employee if provided
            if ($employeeId) {
                $employeeQuery->where('id', $employeeId);
            }

            $employees = $employeeQuery->get();

            if ($employees->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No employees found with salary details configured for the selected filters.'
                ], 404);
            }

            // Calculate date range for the month
            $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
            $totalDaysInMonth = $startDate->daysInMonth;

            $results = [
                'total' => $employees->count(),
                'successful' => 0,
                'failed' => 0,
                'skipped' => 0,
                'errors' => []
            ];

            // Process each employee
            foreach ($employees as $employee) {
                try {
                    $employeeId = $employee->id;
                    $employeeBranchId = $employee->branch_id;
                    $employeeDepartmentId = $employee->employmentDetail?->department_id;

                    // Check if salary already exists and is locked
                    $existingSalary = Salary::where('company_id', $companyId)
                        ->where('employee_id', $employeeId)
                        ->where('year', $year)
                        ->where('month', $month)
                        ->first();

                    if ($existingSalary && $existingSalary->is_locked) {
                        $results['skipped']++;
                        $results['errors'][] = [
                            'employee' => $employee->full_name . ' (' . $employee->employee_code . ')',
                            'message' => 'Salary is locked and cannot be recalculated'
                        ];
                        continue;
                    }

                    // Get salary details for employee (dynamically applying any active increment for the month/year)
                    $salaryDetail = $this->getApplicableSalaryDetail($companyId, $employeeId, $year, $month);
                    if (!$salaryDetail) {
                        $results['failed']++;
                        $results['errors'][] = [
                            'employee' => $employee->full_name . ' (' . $employee->employee_code . ')',
                            'message' => 'Salary details not found'
                        ];
                        continue;
                    }

                    // Capture snapshots
                    $snapshots = $this->captureDataSnapshots($companyId, $employeeId, $startDate, $endDate, $salaryDetail);

                    // Get holiday dates & leave dates (For Exclusion Logic)
                    $holidayDates = $this->getHolidayDates($companyId, $startDate, $endDate);
                    $leaveDates = $this->getLeaveDates($companyId, $employeeId, $startDate, $endDate);

                    // Get attendance data
                    $attendanceData = $this->getAttendanceData($companyId, $employeeId, $startDate, $endDate, $salaryDetail, $holidayDates, $leaveDates);

                    // Get holiday count
                    $holidayCount = $this->getHolidayCount($companyId, $startDate, $endDate);

                    // Get leave data
                    $leaveData = $this->getLeaveData($companyId, $employeeId, $startDate, $endDate);

                    // Get bonus data
                    $bonusData = $this->getBonusData($companyId, $employeeId, $employeeBranchId, $year, $month);

                    // Get loan EMI data
                    $loanData = $this->getLoanData($companyId, $employeeId);

                    // Calculate per day salary
                    $ctc = $salaryDetail->ctc ?? 0;

                    // Determine divisor
                    $monthCountType = trim($salaryDetail->salary_calculation_month_count ?? '');
                    $monthCountTypeNormalized = strtolower($monthCountType);
                    $salaryDivisor = 30; // Default

                    if (str_contains($monthCountTypeNormalized, 'total days')) {
                        $salaryDivisor = $totalDaysInMonth;
                        if (str_contains($monthCountTypeNormalized, 'week off')) {
                            $salaryDivisor = $totalDaysInMonth - ($attendanceData['total_week_off'] ?? 0);
                        }
                    }
                    if ($salaryDivisor <= 0)
                        $salaryDivisor = 30;

                    $perDaySalary = $ctc / $salaryDivisor;

                    // Get operation earnings total
                    $operationEarnings = OperationsRateList::where('company_id', $companyId)
                        ->where('employee_id', $employeeId)
                        ->where('month', $month)
                        ->where('year', $year)
                        ->sum('total_amount');

                    // Calculate salary components
                    $calculatedData = $this->calculateSalaryComponents(
                        $salaryDetail,
                        $attendanceData,
                        $holidayCount,
                        $leaveData,
                        $bonusData,
                        $loanData,
                        $totalDaysInMonth,
                        $perDaySalary,
                        $salaryDivisor,
                        $employee,
                        $startDate,
                        $endDate,
                        $operationEarnings
                    );
                    // Prepare salary data
                    $salaryData = array_merge($calculatedData, [
                        'company_id' => $companyId,
                        'branch_id' => $employeeBranchId,
                        'department_id' => $employeeDepartmentId,
                        'employee_id' => $employeeId,
                        'year' => $year,
                        'month' => $month,
                        'added_date' => now()->format('Y-m-d'),
                        'created_by' => $loginUserId,
                        'status' => 'active',
                        'calculation_date' => now(),
                        'attendance_snapshot_date' => $snapshots['attendance_snapshot_date'],
                        'attendance_records_count' => $snapshots['attendance_records_count'],
                        'leave_snapshot_date' => $snapshots['leave_snapshot_date'],
                        'leave_records_count' => $snapshots['leave_records_count'],
                        'holiday_snapshot_date' => $snapshots['holiday_snapshot_date'],
                        'holiday_records_count' => $snapshots['holiday_records_count'],
                        'salary_detail_snapshot_date' => $snapshots['salary_detail_snapshot_date'],
                        'data_modified' => false,
                        'data_modification_details' => null,
                    ]);

                    // Update or create salary record
                    if ($existingSalary) {
                        $existingSalary->update($salaryData);
                        $salary = $existingSalary;
                    } else {
                        $salary = Salary::create($salaryData);
                    }

                    $this->pushSalaryToOldCRM($salary);

                    $results['successful']++;

                } catch (\Exception $e) {
                    $results['failed']++;
                    $results['errors'][] = [
                        'employee' => $employee->full_name . ' (' . $employee->employee_code . ')',
                        'message' => $e->getMessage()
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Bulk salary calculation completed',
                'data' => $results
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error in bulk calculation: ' . $e->getMessage()
            ], 500);
        }
    }

    public function exportExcel(Request $request)
    {
        $modules = $this->modules;
        $modules['authLoginUserDetail'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails : null;
        $modules['company_id'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails?->company_id : null;

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

        return Excel::download(new SalaryExport($request->all(), $this->authenticateLoginUserDetails, $modules), 'Salary-Calculation-' . Helper::convert_date("", "Y-m-d H:i:s", "Ymd-His") . '.xlsx');
    }

    public function exportBankTransferExcel(Request $request)
    {
        $modules = $this->modules;
        $modules['authLoginUserDetail'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails : null;
        $modules['company_id'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails?->company_id : null;

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

        return Excel::download(
            new SalaryBankTransferExport($request->all(), $this->authenticateLoginUserDetails, $modules),
            'Salary-Bank-Transfer-' . Helper::convert_date("", "Y-m-d H:i:s", "Ymd-His") . '.xlsx'
        );
    }

    /**
     * Get employees grouped by department
     */
    public function getEmployeesByDepartment(Request $request)
    {
        $companyId = $request->company_id;
        // Security: Enforce company_id for restricted users
        if ($this->authenticateLoginUserDetails && $this->authenticateLoginUserDetails->company_id) {
            $companyId = $this->authenticateLoginUserDetails->company_id;
        }
        $branchId = $request->branch_id;
        $departmentId = $request->department_id;

        if (!$companyId) {
            return response()->json([
                'status' => false,
                'message' => 'Company ID is required',
                'data' => []
            ]);
        }

        try {
            // Get employees with their departments and all salary details
            $query = Employee::where('company_id', $companyId)
                ->where('status', 'active')
                ->with(['employmentDetail.department', 'salary_details']);

            // Filter by branch if provided
            if ($branchId) {
                // Allow employees with the selected branch OR no branch assigned (NULL)
                $query->where(function ($q) use ($branchId) {
                    $q->where('branch_id', $branchId)
                        ->orWhereNull('branch_id');
                });
            }

            // Filter by department if provided
            if ($departmentId) {
                $query->whereHas('employmentDetail', function ($q) use ($departmentId) {
                    $q->where('department_id', $departmentId);
                });
            }

            // Exclude contractor employees when requested (e.g. Leave Summary Report)
            if ($request->has('exclude_contractor') && $request->exclude_contractor) {
                $contractTypeIds = \App\Models\EmployeeType::where('name', 'like', '%contract%')->pluck('id');
                if ($contractTypeIds->isNotEmpty()) {
                    $query->whereDoesntHave('employmentDetail', function ($q) use ($contractTypeIds) {
                        $q->whereIn('employment_type', $contractTypeIds);
                    });
                }
            }

            $employees = $query->orderBy('full_name', 'ASC')->get();

            // Group employees by department
            $groupedEmployees = [];

            foreach ($employees as $employee) {
                $deptName = 'No Department';

                if ($employee->employmentDetail && $employee->employmentDetail->department) {
                    $deptName = $employee->employmentDetail->department->name;
                }

                // Fetch salary calculation month count - Robust Logic
                $monthCount = '';
                // Get the latest salary detail record by ID or Created At
                $latestSalaryDetail = $employee->salary_details->sortByDesc('id')->first();
                if ($latestSalaryDetail) {
                    $monthCount = trim($latestSalaryDetail->salary_calculation_month_count ?? '');
                }

                if (!isset($groupedEmployees[$deptName])) {
                    $groupedEmployees[$deptName] = [];
                }

                $groupedEmployees[$deptName][] = [
                    'id' => $employee->id,
                    'employee_code' => $employee->employee_code,
                    'full_name' => $employee->full_name,
                    'branch_id' => $employee->branch_id,
                    'salary_calculation_month_count' => $monthCount,
                ];
            }

            // Sort departments alphabetically
            ksort($groupedEmployees);

            return response()->json([
                'status' => true,
                'message' => 'Employees fetched successfully',
                'data' => $groupedEmployees,
                'total_count' => $employees->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error fetching employees: ' . $e->getMessage(),
                'data' => []
            ]);
        }
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
            $in = Carbon::parse($inTime);
            $out = Carbon::parse($outTime);

            // If out time is before in time, assume next day
            if ($out->lt($in)) {
                $out->addDay();
            }

            return $in->diffInMinutes($out);
        } catch (\Exception $e) {
            return 0;
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

        // Handle datetime format with space
        if (strpos($timeString, ' ') !== false) {
            $parts = explode(' ', $timeString);
            $timeString = $parts[1] ?? $parts[0];
        }

        $parts = explode(':', $timeString);
        $hours = isset($parts[0]) ? (int) $parts[0] : 0;
        $minutes = isset($parts[1]) ? (int) $parts[1] : 0;
        $seconds = isset($parts[2]) ? (int) $parts[2] : 0;

        return ($hours * 60) + $minutes + floor($seconds / 60);
    }

    /**
     * Extract time part from datetime string (match AttendanceReportController for consistent sorting)
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
     * Get list of holiday dates in Y-m-d format
     */
    private function getHolidayDates($companyId, $startDate, $endDate)
    {
        $holidays = Holiday::where('company_id', $companyId)
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('from_date', [$startDate, $endDate])
                    ->orWhereBetween('to_date', [$startDate, $endDate])
                    ->orWhere(fn($q2) => $q2->where('from_date', '<', $startDate)->where('to_date', '>', $endDate));
            })->get();

        $dates = [];
        foreach ($holidays as $h) {
            $from = Carbon::parse($h->getRawOriginal('from_date'))->startOfDay();
            $to = Carbon::parse($h->getRawOriginal('to_date'))->startOfDay();

            // Clamp
            if ($from->lt($startDate))
                $from = $startDate->copy();
            if ($to->gt($endDate))
                $to = $endDate->copy();

            for ($d = $from->copy(); $d->lte($to); $d->addDay()) {
                $dates[$d->format('Y-m-d')] = true;
            }
        }
        return $dates;
    }

    /**
     * Get list of approved leave dates in Y-m-d format
     */
    private function getLeaveDates($companyId, $employeeId, $startDate, $endDate)
    {
        $leaves = LeaveApplication::where('company_id', $companyId)
            ->where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('fromdate_time', [$startDate, $endDate])
                    ->orWhereBetween('todate_time', [$startDate, $endDate])
                    ->orWhere(fn($q2) => $q2->where('fromdate_time', '<', $startDate)->where('todate_time', '>', $endDate));
            })->get();

        $dates = [];
        foreach ($leaves as $leave) {
            $from = Carbon::parse($leave->fromdate_time)->startOfDay();
            $to = Carbon::parse($leave->todate_time ?? $leave->fromdate_time)->startOfDay();

            // Clamp
            if ($from->lt($startDate))
                $from = $startDate->copy();
            if ($to->gt($endDate))
                $to = $endDate->copy();

            for ($d = $from->copy(); $d->lte($to); $d->addDay()) {
                $dates[$d->format('Y-m-d')] = true;
            }
        }
        return $dates;
    }

    /**
     * Push salary data to Old CRM if configured
     */
    private function pushSalaryToOldCRM($salary)
    {
        try {
            // Find an active biometric machine of type 'old_crm' for this company
            $machine = BiometricMachine::where('company_id', $salary->company_id)
                ->where('provider_type', 'old_crm')
                ->where('status', 'active')
                ->first();

            if (!$machine) {
                return; // No Old CRM integration for this company
            }

            // Load employee if not loaded
            if (!$salary->relationLoaded('employee')) {
                $salary->load('employee');
            }

            $service = BiometricServiceFactory::make('old_crm', $machine);

            $data = [
                'employee_code' => $salary->employee->employee_code,
                'year' => (int) $salary->year,
                'month' => (int) $salary->month,
                'net_pay' => (float) $salary->net_bank_pay,
                'present_days' => (float) $salary->total_present_day,
                'total_days' => (int) $salary->total_day,
                'ctc' => (float) $salary->ctc,
                'total_earning' => (float) $salary->total_earning,
                'total_deduction' => (float) $salary->total_deduction,
                'calculation_date' => $salary->calculation_date ? $salary->calculation_date->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s'),
            ];

            $service->pushSalaryData($data);
        } catch (\Exception $e) {
            Log::error('Failed to push salary to Old CRM in controller: ' . $e->getMessage());
        }
    }
}
