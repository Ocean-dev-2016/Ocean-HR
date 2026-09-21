<?php

namespace App\Http\Controllers\software;

use App\Http\Controllers\Controller;
use App\Models\EmployeeImportFile;
use Illuminate\Http\Request;
use App\Exports\EmployeeExport;
use App\Exports\ComprehensiveEmployeeExport;
use App\Http\Requests\EmployeeRequest;
use App\Models\Employee;
use App\Helpers\Helper;
use App\Http\Controllers\CommonController;
use App\Imports\EmployeeImport;
use App\Models\TeamRole;
use App\Models\Company;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ContractorEmployeeController extends Controller
{

    public $modules = [];
    public function __construct()
    {
        parent::__construct();

        $this->modules = [
            'title' => 'Contractor Employee',
            'folder_path' => 'software.modules.contractor.employee',
            'route' => 'contractor-employees',
            'table_name' => (new Employee())->getTable(),
            'permisstion_prefix' => 'contractor-employees',
            'module_name' => 'Contractor Employee',
            'company_id' => ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails?->company_id : null,

        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $modules = $this->modules;
        $modules['authLoginUserDetail'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails : null;
        $modules['currentGuard'] = ($this->currentGuard) ? $this->currentGuard : null;
        $modules['company_id'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails?->company_id : null;
        $modules['parent_type_id'] = ($this->authenticateLoginUserDetails?->parent_type_id) ? $this->authenticateLoginUserDetails?->parent_type_id : null;
        $loginUserId = ($modules['authLoginUserDetail'] && $modules['authLoginUserDetail']?->id) ? $modules['authLoginUserDetail']?->id : null;

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

        try {
            // define columns
            $columns = [
                // (object)['data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'td_label' => 'Sr No.', 'orderable' => false, 'searchable' => false],
                (object) ['data' => 'employee_code', 'name' => $modules['table_name'] . '.employee_code', 'td_label' => 'Employee Code', 'className' => 'w-20', 'orderable' => true, 'searchable' => false],
                (object) ['data' => 'company_name', 'name' => (new Company())->getTable() . '.company_name', 'td_label' => 'Company Name', 'className' => '', 'orderable' => true, 'searchable' => false],
                // (object)['data' => 'first_name', 'name' => 'employees.first_name', 'td_label' => 'First Name', 'className' => '', 'orderable' => false, 'searchable' => false],
                // (object)['data' => 'father_name', 'name' => 'employees.father_name', 'td_label' => 'Father Name', 'className' => '', 'orderable' => false, 'searchable' => false],
                (object) ['data' => 'full_name', 'name' => $modules['table_name'] . '.full_name', 'td_label' => 'Full Name', 'className' => 'w-40 text-wrap', 'orderable' => true, 'searchable' => false],
                (object) ['data' => 'team_role_name', 'name' => 'team_role.name', 'td_label' => 'Team Role', 'className' => '', 'orderable' => true, 'searchable' => false],


                // (object)['data' => 'parent_name', 'name' => $modules['table_name'] . '.parent_id', 'td_label' => 'Perent Name', 'className' => '', 'orderable' => true, 'searchable' => false],

                // (object)['data' => 'contact_number', 'name' => $modules['table_name'] . '.contact_number', 'td_label' => 'Contact Number', 'className' => 'text-wrap text-center', 'orderable' => true, 'searchable' => false],
                // (object)['data' => 'email', 'name' => $modules['table_name'] . '.email', 'td_label' => 'Email', 'className' => ' text-wrap text-center', 'orderable' => true, 'searchable' => false],
                (object) ['data' => 'username', 'name' => $modules['table_name'] . '.username', 'td_label' => ($modules['currentGuard'] === 'admin_software') ? 'Login Details' : 'Username', 'className' => ' text-wrap text-left', 'orderable' => true, 'searchable' => false],
                // (object)['data' => 'sp', 'name' => $modules['table_name'] . '.sp', 'td_label' => 'SP', 'className' => ' text-wrap text-center', 'orderable' => true, 'searchable' => false],
                // (object)['data' => 'created_at', 'name' => $modules['table_name'] . '.created_at', 'td_label' => 'Register Date', 'className' => 'w-5 text-start', 'orderable' => true, 'searchable' => false],
                (object) ['data' => 'action', 'name' => 'action', 'td_label' => 'Action', 'orderable' => false, 'searchable' => false, 'className' => 'text-start'],
            ];

            if ($modules['company_id']) {
                $columns = array_filter($columns, fn($col) => $col->name !== 'companies.company_name');
                $columns = array_values($columns);

                $columns = array_filter($columns, fn($col) => $col->name !== $modules['table_name'] . '.sp');
                $columns = array_values($columns);
            }

            View::share("columns", $columns);

            if ($request->ajax()) {
                // build query with join for sorting/search
                $data = Employee::select('employees.*', 'companies.company_name', 'team_role.name as team_role_name')
                    ->leftJoin('companies', 'companies.id', '=', 'employees.company_id')
                    ->leftJoin('team_role', 'team_role.id', '=', 'employees.role_id')
                    ->when(Auth::guard('employees')->check() || !empty($modules['company_id']), function ($query) use ($modules, $loginUserId) {

                        // If company_id exists in $modules, use that; otherwise use employee's company_id
                        $companyId = $modules['company_id'] ?? Auth::guard('employees')->user()->company_id;

                        $query->where($modules['table_name'] . '.company_id', $companyId);

                        // Personal data permission rule
                        if ($modules['personal_data_permission'] && $modules['all_data_permission'] == false) {
                            $query->where($modules['table_name'] . '.created_by', $loginUserId);
                        }
                    });

                // Show only Contractor Salary / Contract types
                $contractTypeId = \App\Models\EmployeeType::where('name', 'like', '%contract%')->orWhere('name', 'like', '%contractor%')->pluck('id');
                $data->whereHas('employmentDetail', function ($q) use ($contractTypeId) {
                    $q->whereIn('employment_type', $contractTypeId);
                });

                if (isset($modules['restore_permission']) && $modules['restore_permission']) {
                    $data = $data->withTrashed();
                }
                $data = $data->orderBy($modules['table_name'] . '.created_at', 'desc');
                $returnData = Datatables::of($data)
                    ->addIndexColumn()
                    ->filter(function ($query) use ($request, $modules) {
                        if ($request->has('filter_company') && $request->filter_company) {
                            $query->where($modules['table_name'] . '.company_id', $request->filter_company);
                        }
                        if ($request->has('filter_branch') && $request->filter_branch) {
                            $query->where($modules['table_name'] . '.branch_id', $request->filter_branch);
                        }
                        if ($request->has('filter_department') && $request->filter_department) {
                            $query->whereHas('employmentDetail', function ($q) use ($request) {
                                $q->where('department_id', $request->filter_department);
                            });
                        }
                        if ($request->has('filter_parent') && $request->filter_parent) {
                            $query->where($modules['table_name'] . '.parent_id', $request->filter_parent);
                        }

                        if ($request->has('status') && $request->status !== null && $request->status !== 'all') {
                            $query->where($modules['table_name'] . '.status', $request->status);
                        }

                        if ($request->has('search') && $request->search) {
                            $query->where(function ($q) use ($request) {
                                $q->where('employee_code', 'like', "%" . $request->search . "%")
                                    ->orWhere('full_name', 'like', "%" . $request->search . "%")
                                    ->orWhere('contact_number', 'like', "%" . $request->search . "%");
                            });
                        }
                    });
                // Enable ordering for both columns
                // $returnData = $returnData->orderColumn('name', 'branches.name $1')->orderColumn('company_name', 'companies.company_name $1');

                foreach ($columns as $column) {
                    if (!empty($column->orderable) && $column->orderable === true) {
                        $returnData->orderColumn($column->data, "{$column->name} \$1");
                    }
                }

                $returnData = $returnData->editColumn('date', function ($row) {
                    return \Carbon\Carbon::parse($row->date)->format('d/m/Y');
                })
                    ->editColumn('created_at', function ($row) {
                        return \Carbon\Carbon::parse($row->created_at)->format('d-m-Y H:i:s');
                    })
                    ->editColumn('employee_code', function ($row) {
                        $employeeCode = $row->employee_code ?? '-';
                        $bioUserId = $row->biometric_user_id ?? 'N/A';

                        $html = '<div style="line-height: 1.6;">';
                        $html .= '<div><small class="text-muted">Employee Code</small> <strong>' . e($employeeCode) . '</strong></div>';
                        $html .= '<div><small class="text-muted">Bio User ID</small> <strong>' . e($bioUserId) . '</strong></div>';
                        $html .= '</div>';
                        return $html;
                    })
                    ->editColumn('full_name', function ($row) {
                        $fullName = $row->full_name ?? '-';
                        $contactNumber = $row->contact_number ?? null;
                        $email = $row->email ?? null;

                        $html = '<div style="line-height: 1.6;">';
                        $html .= '<div><small class="text-muted">Full Name</small> <strong>' . e($fullName) . '</strong></div>';
                        $html .= '<div><small class="text-muted">Number</small> <a href="tel:' . e($contactNumber) . '"><strong>' . e($contactNumber) . '</strong></a></div>';
                        $html .= '<div><small class="text-muted">Email</small> <a href="mailto:' . e($email) . '"><strong>' . e($email) . '</strong></a></div>';
                        $html .= '</div>';

                        return $html;
                    })
                    ->editColumn('username', function ($row) use ($modules) {
                        $username = $row->username ?? '-';

                        // Only show login details for admin_software guard
                        if ($modules['currentGuard'] === 'admin_software') {
                            $appKey = $row->company->app_key ?? '-';
                            $userSp = $row->sp ?? '-';

                            $loginDetails = "App Key: {$appKey}\nUsername: {$username}\nPassword: {$userSp}";

                            $html = '<div class="login-details-wrapper">';
                            $html .= '<div class="mb-1"><small class="text-muted">App Key:</small> <strong>' . e($appKey) . '</strong></div>';
                            $html .= '<div class="mb-1"><small class="text-muted">Username:</small> <strong>' . e($username) . '</strong></div>';
                            $html .= '<div class="mb-1"><small class="text-muted">Password:</small> <strong>' . e($userSp) . '</strong></div>';
                            $html .= '<button type="button" class="btn btn-sm btn-outline-primary copy-login-details mt-1" ';
                            $html .= 'data-app-key="' . e($appKey) . '" ';
                            $html .= 'data-username="' . e($username) . '" ';
                            $html .= 'data-password="' . e($userSp) . '" ';
                            $html .= 'title="Copy & Share">';
                            $html .= '<i class="ti ti-copy"></i> Copy Detail</button>';
                            $html .= '</div>';

                            return $html;
                        }

                        return $username;
                    })
                    ->editColumn('status', function ($row) use ($modules) {
                        $btn = '';
                        $dropdown = '<ul class="dropdown-menu">';
                        if ($row->status == "active") {
                            $btn .= '<button type="button" class="btn btn-success btn-sm  dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">' . ucfirst($row->status) . '</button>';
                            $dropdown .= '<li><a href="javascript:void(0)" class="dropdown-item update-status" data-url="' . route($modules["route"] . '.status-update') . '" data-id="' . $row->id . '" data-update_status="inactive">Inactive</a></li>';
                            $dropdown .= '<li><a href="javascript:void(0)" class="dropdown-item update-status" data-url="' . route($modules["route"] . '.status-update') . '" data-id="' . $row->id . '" data-update_status="resigned">Resigned</a></li>';
                        } elseif ($row->status == "inactive") {
                            $btn .= '<button type="button" class="btn btn-danger btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">' . ucfirst($row->status) . '</button>';
                            $dropdown .= '<li><a href="javascript:void(0)" class="dropdown-item update-status" data-url="' . route($modules["route"] . '.status-update') . '" data-id="' . $row->id . '" data-update_status="active">Active</a></li>';
                            $dropdown .= '<li><a href="javascript:void(0)" class="dropdown-item update-status" data-url="' . route($modules["route"] . '.status-update') . '" data-id="' . $row->id . '" data-update_status="resigned">Resigned</a></li>';
                        } elseif ($row->status == "resigned") {
                            $btn .= '<button type="button" class="btn btn-warning btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">' . ucfirst($row->status) . '</button>';
                            $dropdown .= '<li><a href="javascript:void(0)" class="dropdown-item update-status" data-url="' . route($modules["route"] . '.status-update') . '" data-id="' . $row->id . '" data-update_status="active">Active</a></li>';
                            $dropdown .= '<li><a href="javascript:void(0)" class="dropdown-item update-status" data-url="' . route($modules["route"] . '.status-update') . '" data-id="' . $row->id . '" data-update_status="inactive">Inactive</a></li>';
                        } else {
                            return $btn;
                        }
                        $dropdown .= '</ul>';
                        return $btn . $dropdown;
                    })
                    ->addColumn('parent_name', function ($row) {
                        return optional($row->parentEmployee)->full_name ?? '-';
                    })

                    ->addColumn('action', function ($row) use ($modules) {
                        $btn = '';
                        $dropdown = '<ul class="dropdown-menu">';
                        if ($row->status == "active") {
                            $btn .= '<button type="button" class="btn btn-success btn-sm  dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">' . ucfirst($row->status) . '</button>';
                            $dropdown .= '<li><a href="javascript:void(0)" class="dropdown-item update-status" data-url="' . route($modules["route"] . '.status-update') . '" data-id="' . $row->id . '" data-update_status="inactive">Inactive</a></li>';
                            $dropdown .= '<li><a href="javascript:void(0)" class="dropdown-item update-status" data-url="' . route($modules["route"] . '.status-update') . '" data-id="' . $row->id . '" data-update_status="resigned">Resigned</a></li>';
                        } elseif ($row->status == "inactive") {
                            $btn .= '<button type="button" class="btn btn-danger btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">' . ucfirst($row->status) . '</button>';
                            $dropdown .= '<li><a href="javascript:void(0)" class="dropdown-item update-status" data-url="' . route($modules["route"] . '.status-update') . '" data-id="' . $row->id . '" data-update_status="active">Active</a></li>';
                            $dropdown .= '<li><a href="javascript:void(0)" class="dropdown-item update-status" data-url="' . route($modules["route"] . '.status-update') . '" data-id="' . $row->id . '" data-update_status="resigned">Resigned</a></li>';
                        } elseif ($row->status == "resigned") {
                            $btn .= '<button type="button" class="btn btn-warning btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">' . ucfirst($row->status) . '</button>';
                            $dropdown .= '<li><a href="javascript:void(0)" class="dropdown-item update-status" data-url="' . route($modules["route"] . '.status-update') . '" data-id="' . $row->id . '" data-update_status="active">Active</a></li>';
                            $dropdown .= '<li><a href="javascript:void(0)" class="dropdown-item update-status" data-url="' . route($modules["route"] . '.status-update') . '" data-id="' . $row->id . '" data-update_status="inactive">Inactive</a></li>';
                        } else {
                            return $btn;
                        }
                        $dropdown .= '</ul><br><div class="mb-2"></div>';
                        $btn .= $dropdown;
                        $btn .= '<a href="' . route($modules["route"] . ".show", [$row->id]) . '" class="btn btn-sm btn-info btn-icon mx-1"><i class="fa-solid fa-eye"></i></a>';
                        if (!$row?->deleted_at) {
                            $btn .= '<a href="javascript:void(0)" data-id="' . $row->id . '" data-resign-date="' . ($row->resign_date ?? '') . '" class="btn btn-sm btn-warning btn-icon mx-1 resign-date-btn" data-bs-toggle="modal" data-bs-target="#resignDateModal" title="Resign Date"><i class="fa-solid fa-calendar-times"></i></a>';
                            if ($modules['update_permission']) {
                                $btn .= '<a href="' . route($modules["route"] . ".edit", [$row->id]) . '" class="btn btn-sm btn-light btn-icon mx-1"><i class="fa-solid fa-pen-to-square"></i></a>';
                            }
                            if ($modules['delete_permission']) {
                                $btn .= '<a href="javascript:void(0)" data-id="' . $row->id . '" data-did="' . route($modules["route"] . ".destroy", [$row->id]) . '" class="btn btn-sm btn-danger btn-icon deletebutton mx-1"><i class="fa-solid fa-trash"></i></a>';
                            }
                        } else {
                            $btn .= '<a href="javascript:void(0)" data-restore="' . route($modules["route"] . ".restore", ['id' => $row->id]) . '" class="btn btn-sm btn-light mx-1 record-restore"><i class="ti ti-history"></i> Restore</a>';
                        }
                        return $btn ?: '-';
                    })
                    ->rawColumns(['employee_code', 'full_name', 'login_detail', 'action', 'username'])
                    ->make(true);

                return $returnData;
            }

            return view($modules['folder_path'] . '.index');
        } catch (\Exception $e) {
            return Redirect::route('software.dashboard')->withErrors($e->getMessage());
        }
    }


    public function create(Request $request)
    {
        $modules = $this->modules;
        $modules['addPermission'] = Gate::check('hasPermission', ['add', $modules['module_name']]);
        $modules['authLoginUserDetail'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails : null;
        $modules['currentGuard'] = ($this->currentGuard) ? $this->currentGuard : null;
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

        View::share('modules', $modules);

        try {
            $team_roles = TeamRole::where('status', 'active');
            if ($modules['company_id']) {
                $team_roles->where('company_id', $modules['company_id']);
            }
            $team_roles = $team_roles->get();
            View::share('team_roles', $team_roles);

            return view($modules['folder_path'] . '.form');
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }


    public function store(EmployeeRequest $request)
    {
        $modules = $this->modules;
        $modules['authLoginUserDetail'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails : null;
        $modules['currentGuard'] = ($this->currentGuard) ? $this->currentGuard : null;
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

        View::share('modules', $modules);
        // $validated = $request->validated();
        $validated = array_merge($request->all(), $request->validated());
        try {
            $validated['created_by'] = $loginUserId;

            $newRequest = new Request();
            $newRequest['company_id'] = $modules['company_id'];
            $newRequest['company_id'] = (isset($newRequest['company_id']) && !empty($newRequest['company_id'])) ? $newRequest['company_id'] : $request?->company_id;
            if ($request->branch_id) {
                $newRequest['branch_id'] = $request?->branch_id;
            }

            // Determine company and its employee code setting
            $companyIdForEmployee = $newRequest['company_id'] ?? null;
            $company = $companyIdForEmployee ? Company::find($companyIdForEmployee) : null;

            // Only auto-generate employee code if company is NOT set to manual
            if (!$company || $company->employee_code_auto_generation !== 'manual') {
                $generate_employee_code = (new CommonController(new Request()))->generate_employee_code($newRequest);
                // dd("L-410", $validated, $modules['company_id'], $newRequest, $generate_employee_code);

                if ($generate_employee_code->getStatusCode() == 200) {
                    $dataResponse = $generate_employee_code?->getData();
                    if ($dataResponse?->data) {
                        $validated['employee_code'] = $dataResponse?->data;
                    }
                }
            }

            $validated['created_by'] = $loginUserId;
            $validated['password'] = Hash::make($request?->password);
            $validated['sp'] = Helper::generateSP($request?->password);
            // dd($request->all(), $validated);
            $employee = Employee::create($validated);

            if ($employee) {
                $targetCompanyId = $employee->company_id ?? $modules['company_id'];
                $contractType = \App\Models\EmployeeType::where('company_id', $targetCompanyId)
                    ->where(function ($q) {
                        $q->where('name', 'like', '%contract%')
                            ->orWhere('name', 'like', '%contractor%');
                    })
                    ->first();

                if (!$contractType) {
                    $contractType = \App\Models\EmployeeType::create([
                        'company_id' => $targetCompanyId,
                        'name' => 'Contractor Salary',
                        'status' => 'active',
                        'created_by' => $loginUserId,
                    ]);
                }

                $firstDept = \App\Models\Department::where('company_id', $targetCompanyId)->first();
                $firstDesig = \App\Models\Designation::where('company_id', $targetCompanyId)->first();
                $firstShift = \App\Models\Shift::where('company_id', $targetCompanyId)->first();

                \App\Models\EmploymentDetail::firstOrCreate(
                    ['employee_id' => $employee->id],
                    [
                        'company_id' => $targetCompanyId,
                        'designation_type' => 'employee',
                        'department_id' => $firstDept?->id ?: 1,
                        'designation_id' => $firstDesig?->id ?: 1,
                        'shift' => $firstShift?->id ?: 1,
                        'date_of_joining' => Carbon::now()->format('Y-m-d'),
                        'payment_mode' => 'NEFT',
                        'employment_type' => $contractType->id,
                        'outdoor_attendance' => 'no',
                        'status' => 'active',
                        'created_by' => $loginUserId,
                    ]
                );
            }

            return Redirect::route($modules['route'] . '.index')->withSuccess($modules['title'] . ' create successfully');
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $modules = $this->modules;
        $modules['authLoginUserDetail'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails : null;
        $modules['currentGuard'] = ($this->currentGuard) ? $this->currentGuard : null;
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

        if ($request->ajax()) {
            $month = $request->input('month', now()->month);
            $year = $request->input('year', now()->year);

            $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
            $today = Carbon::today();

            // Get week offs
            $salaryDetail = \App\Models\EmployeeWiseSalaryDetail::where('employee_id', $id)->first();
            $woJson = $salaryDetail?->week_off ?? '[]';
            $woArray = json_decode($woJson, true);
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
            $employeeWeekOffDays = [];
            if (is_array($woArray)) {
                foreach ($woArray as $wo) {
                    $woStr = strtolower(trim($wo));
                    if (isset($dayMap[$woStr])) {
                        $employeeWeekOffDays[] = $dayMap[$woStr];
                    }
                }
            }

            // Fetch attendances for this month
            $attendances = \App\Models\Attendance::where('employee_id', $id)
                ->whereBetween('attendance_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->orderBy('punch_in_time', 'asc')
                ->get()
                ->groupBy('attendance_date');

            // Fetch holidays
            $holidays = \App\Models\Holiday::where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('from_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                    ->orWhereBetween('to_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
            })->get();

            $holidayDates = [];
            foreach ($holidays as $h) {
                $from = Carbon::parse($h->from_date)->startOfDay();
                $to = Carbon::parse($h->to_date)->startOfDay();
                for ($d = $from; $d->lte($to); $d->addDay()) {
                    $holidayDates[$d->format('Y-m-d')] = $h->holiday_label;
                }
            }

            // Fetch approved leaves
            $leaves = \App\Models\LeaveApplication::where('employee_id', $id)
                ->where('status', 'Approved')
                ->where(function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('fromdate_time', [$startDate->format('Y-m-d H:i:s'), $endDate->format('Y-m-d H:i:s')])
                        ->orWhereBetween('todate_time', [$startDate->format('Y-m-d H:i:s'), $endDate->format('Y-m-d H:i:s')]);
                })->get();

            $leaveDates = [];
            foreach ($leaves as $l) {
                $from = Carbon::parse($l->fromdate_time)->startOfDay();
                $to = Carbon::parse($l->todate_time)->startOfDay();
                for ($d = $from; $d->lte($to); $d->addDay()) {
                    $leaveDates[$d->format('Y-m-d')] = $l;
                }
            }

            // Calculate daily status
            $records = [];
            $stats = [
                'presents' => 0,
                'absents' => 0,
                'leaves' => 0,
                'weekoffs' => 0,
                'holidays' => 0,
                'half_days' => 0,
                'miss_punches' => 0,
                'total_days' => $startDate->daysInMonth
            ];

            for ($day = 1; $day <= $startDate->daysInMonth; $day++) {
                $currentDate = Carbon::createFromDate($year, $month, $day);
                $dateStr = $currentDate->format('Y-m-d');
                $dayOfWeek = $currentDate->dayOfWeek;
                $isFuture = $currentDate->gt($today);

                $punches = $attendances->get($dateStr, collect());
                $leave = $leaveDates[$dateStr] ?? null;
                $isHoliday = isset($holidayDates[$dateStr]);
                $isWeekOff = in_array($dayOfWeek, $employeeWeekOffDays);

                $status = '-';
                $badgeClass = 'bg-secondary';
                $inTime = '-';
                $outTime = '-';
                $workingHours = '-';
                $remarks = '';
                $allPunches = [];

                // If punches exist
                if ($punches->isNotEmpty()) {
                    $punchList = $punches->toArray();
                    $inPunch = null;
                    $outPunch = null;

                    foreach ($punchList as $p) {
                        $pType = strtolower($p['attendace_type'] ?? '');
                        $pTime = $p['punch_in_time'] ?? '';
                        if (strpos($pTime, ' ') !== false) {
                            $pTime = explode(' ', $pTime)[1];
                        }

                        $allPunches[] = [
                            'type' => $p['attendace_type'] ?? 'IN',
                            'time' => $pTime,
                            'device' => $p['device_serial'] ?? 'Biometric'
                        ];

                        if ($pType === 'in' && !$inPunch) {
                            $inPunch = $pTime;
                        }
                        if ($pType === 'out') {
                            $outPunch = $pTime;
                        }
                    }

                    $inTime = $inPunch ?: '-';
                    $outTime = $outPunch ?: '-';

                    $punchCount = $punches->count();
                    $isMissPunch = (!$isFuture && $punchCount % 2 !== 0);

                    $whMinutes = 0;
                    if ($inPunch && $outPunch) {
                        $inParts = explode(':', $inPunch);
                        $outParts = explode(':', $outPunch);
                        if (count($inParts) >= 2 && count($outParts) >= 2) {
                            $inMins = $inParts[0] * 60 + $inParts[1];
                            $outMins = $outParts[0] * 60 + $outParts[1];
                            if ($outMins < $inMins) {
                                $outMins += 24 * 60;
                            }
                            $whMinutes = $outMins - $inMins;
                            $hours = floor($whMinutes / 60);
                            $mins = $whMinutes % 60;
                            $workingHours = sprintf('%02d:%02d', $hours, $mins);
                        }
                    }

                    if ($isMissPunch) {
                        $status = 'Miss Punch';
                        $badgeClass = 'bg-warning text-white';
                        $stats['miss_punches']++;
                        $stats['absents']++;
                    } elseif ($whMinutes > 0 && $whMinutes < 240) {
                        $status = 'Absent (Short Work)';
                        $badgeClass = 'bg-danger';
                        $stats['absents']++;
                    } elseif ($whMinutes >= 240 && $whMinutes < 480) {
                        $status = 'Half Day';
                        $badgeClass = 'bg-info text-white';
                        $stats['half_days']++;
                        $stats['presents'] += 0.5;
                    } else {
                        $status = 'Present';
                        $badgeClass = 'bg-success';
                        $stats['presents']++;
                    }
                } else {
                    if ($isFuture) {
                        $status = '-';
                        $badgeClass = 'bg-light text-muted';
                    } elseif ($leave) {
                        $status = $leave->halfday_fullday === 'halfday' ? 'Half Day Leave' : 'Leave';
                        $badgeClass = 'bg-purple text-white';
                        $stats['leaves']++;
                    } elseif ($isHoliday) {
                        $status = 'Holiday (' . $holidayDates[$dateStr] . ')';
                        $badgeClass = 'bg-dark';
                        $stats['holidays']++;
                    } elseif ($isWeekOff) {
                        $status = 'Week Off';
                        $badgeClass = 'bg-light text-dark border';
                        $stats['weekoffs']++;
                    } else {
                        $status = 'Absent';
                        $badgeClass = 'bg-danger';
                        $stats['absents']++;
                    }
                }

                $records[] = [
                    'day' => $day,
                    'date' => $currentDate->format('d/m/Y'),
                    'day_name' => $currentDate->format('l'),
                    'status' => $status,
                    'badge_class' => $badgeClass,
                    'in_time' => $inTime,
                    'out_time' => $outTime,
                    'working_hours' => $workingHours,
                    'remarks' => $remarks,
                    'all_punches' => $allPunches
                ];
            }

            return response()->json([
                'success' => true,
                'stats' => $stats,
                'records' => $records
            ]);
        }

        View::share('modules', $modules);

        try {
            $show = Employee::with([
                'company',
                'branch',
                'country',
                'state',
                'city',
                'current_role',
                'employee_asign_assets.assets',
                'increment_details.designation',
                'education_experience_details',
                'salary_details',
                'employment_details.designation',
                'employment_details.department',
                'employment_details.subdepartment',
                'employment_details.process',
                'employment_details.employee_type',
                'employment_details.shiftDetail'
            ]);

            if (!empty($modules['company_id'])) {
                $show->where('company_id', $modules['company_id']);
            }
            $show = $show->findOrFail($id);
            View::share('show', $show);

            return view($modules['folder_path'] . '.employee-detail');
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $id)
    {
        $modules = $this->modules;
        $modules['authLoginUserDetail'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails : null;
        $modules['currentGuard'] = ($this->currentGuard) ? $this->currentGuard : null;
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

            $empQuery = Employee::query();
            if (!empty($modules['company_id'])) {
                $empQuery->where('company_id', $modules['company_id']);
            }
            $edit = $empQuery->findOrFail($id);
            View::share('edit', $edit);

            $team_roles = TeamRole::where('status', 'active');
            if (!empty($modules['company_id'])) {
                $team_roles->where('company_id', $modules['company_id']);
            } else {
                $team_roles->where('company_id', $edit->company_id);
            }
            $team_roles = $team_roles->get();
            View::share('team_roles', $team_roles);

            return view($modules['folder_path'] . '.form');
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(EmployeeRequest $request, string $id)
    {
        $modules = $this->modules;
        $modules['authLoginUserDetail'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails : null;
        $modules['currentGuard'] = ($this->currentGuard) ? $this->currentGuard : null;
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
        View::share('modules', $modules);
        try {
            $validated = array_merge($request->all(), $request->validated());

            /** Gurad wise data update */
            if ($modules['currentGuard'] != "admin_software" && array_key_exists('employee_code', $validated)) {
                // unset($validated['employee_code']);
            }

            if ($request->filled('date')) {
                $validated['date'] = Carbon::createFromFormat('d/m/Y', $request->date)->format('Y-m-d');
            }
            $validated['updated_by'] = $loginUserId;

            if ($request->filled('password')) {
                $validated['password'] = Hash::make($request?->password);
                $validated['sp'] = Helper::generateSP($request?->password);
            }
            // return $validated;
            $empQuery = Employee::query();
            if (!empty($modules['company_id'])) {
                $empQuery->where('company_id', $modules['company_id']);
            }
            $updateData = $empQuery->findOrFail($id);
            // Only hash & set password if user typed it
            if (!empty($request->password)) {
                $validated['password'] = Hash::make($request->password);
            } else {
                unset($validated['password']); // Don't touch the password field
            }

            if ($updateData) {
                unset($validated['id']);
                $updateData->update($validated);

                return Redirect::route($modules['route'] . '.index')->withSuccess($modules['title'] . ' update successfully');
            }
            return Redirect::back()->withErrors('something went wrong please try again later')->withInput();
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
        $modules['currentGuard'] = ($this->currentGuard) ? $this->currentGuard : null;
        $modules['company_id'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails?->company_id : null;
        $modules['parent_type_id'] = ($this->authenticateLoginUserDetails?->parent_type_id) ? $this->authenticateLoginUserDetails?->parent_type_id : null;
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

        $empQuery = Employee::query();
        if (!empty($modules['company_id'])) {
            $empQuery->where('company_id', $modules['company_id']);
        }
        $dataDelete = $empQuery->findOrFail($id);
        $isAjax = ($request->ajax()) ? true : false;
        try {
            if ($dataDelete) {

                $validated['deleted_by'] = $loginUserId;
                $dataDelete->update($validated);

                if ($dataDelete->delete()) {

                    if ($isAjax) {
                        return $this->sendResponse([], $modules['title'] . ' delete successfully');
                    }
                    return true;
                }
            }
            if ($isAjax) {
                return $this->sendResponse([], "something went wrong please try again later");
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
        $modules['currentGuard'] = ($this->currentGuard) ? $this->currentGuard : null;
        $modules['company_id'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails?->company_id : null;
        $modules['parent_type_id'] = ($this->authenticateLoginUserDetails?->parent_type_id) ? $this->authenticateLoginUserDetails?->parent_type_id : null;
        $loginUserId = ($modules['authLoginUserDetail'] && $modules['authLoginUserDetail']?->id) ? $modules['authLoginUserDetail']?->id : null;
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

            $empQuery = Employee::withTrashed();
            if (!empty($modules['company_id'])) {
                $empQuery->where('company_id', $modules['company_id']);
            }
            $restore_data = $empQuery->findOrFail($id);
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
        $modules['currentGuard'] = ($this->currentGuard) ? $this->currentGuard : null;
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

        $validator = Validator::make($request->all(), [
            'id' => ['required', Rule::exists($modules['table_name'], 'id')],
            'update_status' => ['required', 'in:active,inactive,resigned']
        ]);

        if ($validator->fails() && $isAjax) {
            return $this->sendError($validator->messages()->first(), $validator->messages(), [], 401);
        }


        try {
            $empQuery = Employee::withTrashed();
            if (!empty($modules['company_id'])) {
                $empQuery->where('company_id', $modules['company_id']);
            }
            $country = $empQuery->findOrFail($request?->id);
            if ($country) {
                $country->status = $request->update_status;
                $country->save();
                if ($isAjax) {
                    return $this->sendResponse($country, $modules['title'] . ' status update successfully.');
                }
                return Redirect::route($modules['route'] . '.index')->withSuccess($modules['title'] . ' status update successfully.');
            }
            if ($isAjax) {
                return $this->sendError('something went wrong please try again later');
            }
            return Redirect::back()->withErrors('something went wrong please try again later')->withInput();
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

        // Authenticated user details
        $authUser = $this->authenticateLoginUserDetails;
        $modules['authLoginUserDetail'] = $authUser;
        $modules['company_id'] = $authUser?->company_id ?? null;
        $company_id = $modules['company_id'];
        $loginUserId = $authUser?->id ?? null;
        if (count(config('constants.permissions'))) {
            foreach (config('constants.permissions') as $key => $value) {
                $modules[$value . '_permission'] = (isset($modules['company_id']) && !$modules['company_id'])
                    ? true
                    : Gate::check('hasPermission', [$value, $modules['module_name']]);
            }
        }

        // Unauthorized check
        if (!$modules['view_permission']) {
            if ($request->ajax()) {
                return $this->sendError('Unauthorized', [], [], 403);
            }
            abort(403, 'Unauthorized');
        }
        try {
            // Permissions
            $moduleName = $modules['module_name'];

            // Base Query
            $query = Employee::with(['company'])
                ->orderBy('id', 'DESC')
                ->where(function ($q1) use ($modules, $loginUserId) {
                    if (Auth::guard('employees')->check() || !empty($modules['company_id'])) {
                        // If company_id exists in $modules, use that; otherwise use employee's company_id
                        $companyId = $modules['company_id'] ?? Auth::guard('employees')->user()->company_id;

                        $q1->where($modules['table_name'] . '.company_id', $companyId);

                        // Personal data permission rule
                        if ($modules['personal_data_permission'] && $modules['all_data_permission'] == false) {
                            $q1->where($modules['table_name'] . '.created_by', $loginUserId);
                        }
                    }
                });

            // Filter by contractor
            $contractTypeIds = \App\Models\EmployeeType::where('name', 'like', '%contract%')->pluck('id');
            $query->whereHas('employmentDetail', function ($q) use ($contractTypeIds) {
                $q->whereIn('employment_type', $contractTypeIds);
            });

            // 🔍 Filters — same as your DataTable filter logic
            $query->when($request->filled('filter_company'), function ($q) use ($request, $modules) {
                $q->where($modules['table_name'] . '.company_id', $request->filter_company);
            });

            $query->when($request->filled('filter_branch'), function ($q) use ($request, $modules) {
                $q->where($modules['table_name'] . '.branch_id', $request->filter_branch);
            });

            $query->when($request->filled('filter_department') || $request->filled('department'), function ($q) use ($request) {
                $deptId = $request->filter_department ?: $request->department;
                $q->whereHas('employmentDetail', function ($inner) use ($deptId) {
                    $inner->where('department_id', $deptId);
                });
            });

            $query->when($request->filled('filter_parent'), function ($q) use ($request, $modules) {
                $q->where($modules['table_name'] . '.parent_id', $request->filter_parent);
            });

            // Status Filter
            $query->when($request->filled('status') && $request->status !== 'all', function ($q) use ($request, $modules) {
                $q->where($modules['table_name'] . '.status', $request->status);
            });

            // Search Filter
            $query->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;
                $q->where(function ($inner) use ($search) {
                    $inner->where('employee_code', 'like', "%{$search}%")
                        ->orWhere('full_name', 'like', "%{$search}%")
                        ->orWhere('contact_number', 'like', "%{$search}%");
                });
            });

            // Date Filters (if needed)
            if ($request->filled('from_date')) {
                $query->whereDate('employees.date', '>=', $request->from_date);
            }

            if ($request->filled('to_date')) {
                $query->whereDate('employees.date', '<=', $request->to_date);
            }

            // Get filtered results
            $employee = $query->get();

            return view($modules['folder_path'] . '.print', compact('employee', 'company_id', 'modules'));
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }

    public function updateResignDate(Request $request)
    {
        $isAjax = ($request->ajax()) ? true : false;
        if (!$isAjax) {
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            'id' => ['required', Rule::exists('employees', 'id')],
            'resign_date' => ['required', 'date']
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->messages()->first(), $validator->messages(), [], 401);
        }

        try {
            $employee = Employee::findOrFail($request->id);
            $employee->resign_date = Carbon::parse($request->resign_date)->format('Y-m-d');

            // Set status to resigned automatically if the date is passed or today
            if (Carbon::parse($employee->resign_date)->endOfDay()->isPast()) {
                $employee->status = 'resigned';
            }

            $employee->save();

            return $this->sendResponse($employee, 'Resign date updated successfully.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), $e->getMessage(), [], 401);
        }
    }


    public function exportExcel(Request $request)
    {
        $modules = $this->modules;
        $modules['authLoginUserDetail'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails : null;
        $modules['currentGuard'] = ($this->currentGuard) ? $this->currentGuard : null;
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

        return Excel::download(new ComprehensiveEmployeeExport($request->all(), $this->authenticateLoginUserDetails, $modules), 'Contractor Employee-' . Helper::convert_date("", "Y-m-d H:i:s", "Ymd-His") . '.xlsx');
    }
}
