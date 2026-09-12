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

class EmployeeController extends Controller
{

    public $modules = [];
    public function __construct()
    {
        parent::__construct();

        $this->modules = [
            'title' => 'Employee',
            'folder_path' => 'software.modules.employee',
            'route' => 'employees',
            'table_name' => (new Employee())->getTable(),
            'permisstion_prefix' => 'Employees',
            'module_name' => 'employees',
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

                // Show only Company Payroll OR employees without any employment details record or with unassigned employment type
                $payrollTypeId = \App\Models\EmployeeType::where('name', 'Company Payroll')->pluck('id');
                $data->where(function ($query) use ($payrollTypeId) {
                    $query->whereHas('employmentDetail', function ($q) use ($payrollTypeId) {
                        $q->whereIn('employment_type', $payrollTypeId);
                    })
                    ->orWhereDoesntHave('employmentDetail')
                    ->orWhereHas('employmentDetail', function ($q) {
                        $q->whereNull('employment_type');
                    });
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
            Employee::create($validated);

            return Redirect::route($modules['route'] . '.index')->withSuccess($modules['title'] . ' create successfully');
        } catch (\Exception $e) {
            return $e->getMessage();
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

        // Module-wise permissions for related modules
        // Assign Assets permissions
        $modules['assign_assets_add_permission'] = (isset($modules['company_id']) && !$modules['company_id'])
            ? true
            : Gate::check('hasPermission', ['add', 'Assign Assets']);
        $modules['assign_assets_update_permission'] = (isset($modules['company_id']) && !$modules['company_id'])
            ? true
            : Gate::check('hasPermission', ['update', 'Assign Assets']);

        // Increment Details permissions
        $modules['increment_details_add_permission'] = (isset($modules['company_id']) && !$modules['company_id'])
            ? true
            : Gate::check('hasPermission', ['add', 'Increment Details']);
        $modules['increment_details_update_permission'] = (isset($modules['company_id']) && !$modules['company_id'])
            ? true
            : Gate::check('hasPermission', ['update', 'Increment Details']);

        // Education Experience permissions
        $modules['education_experience_add_permission'] = (isset($modules['company_id']) && !$modules['company_id'])
            ? true
            : Gate::check('hasPermission', ['add', 'Education Experience']);
        $modules['education_experience_update_permission'] = (isset($modules['company_id']) && !$modules['company_id'])
            ? true
            : Gate::check('hasPermission', ['update', 'Education Experience']);

        // Employment Details permissions
        $modules['employment_details_add_permission'] = (isset($modules['company_id']) && !$modules['company_id'])
            ? true
            : Gate::check('hasPermission', ['add', 'Employment Details']);
        $modules['employment_details_update_permission'] = (isset($modules['company_id']) && !$modules['company_id'])
            ? true
            : Gate::check('hasPermission', ['update', 'Employment Details']);

        // Salary Details permissions
        $modules['salary_details_add_permission'] = (isset($modules['company_id']) && !$modules['company_id'])
            ? true
            : Gate::check('hasPermission', ['add', 'Salary Details']);
        $modules['salary_details_update_permission'] = (isset($modules['company_id']) && !$modules['company_id'])
            ? true
            : Gate::check('hasPermission', ['update', 'Salary Details']);

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
            // return $show;
            View::share('show', $show);

            return view($modules['folder_path'] . '.employee-detail');
        } catch (\Exception $e) {
            return $e->getMessage();
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

            // 🔍 Filters — same as your DataTable filter logic
            $query->when($request->filled('filter_company'), function ($q) use ($request, $modules) {
                $q->where($modules['table_name'] . '.company_id', $request->filter_company);
            });

            $query->when($request->filled('filter_branch'), function ($q) use ($request, $modules) {
                $q->where($modules['table_name'] . '.branch_id', $request->filter_branch);
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

        return Excel::download(new ComprehensiveEmployeeExport($request->all(), $this->authenticateLoginUserDetails, $modules), 'Employee-' . Helper::convert_date("", "Y-m-d H:i:s", "Ymd-His") . '.xlsx');
    }

    public function import()
    {
        $modules = $this->modules;
        $modules['authLoginUserDetail'] = $this->authenticateLoginUserDetails ?? null;
        $modules['company_id'] = $modules['authLoginUserDetail']?->company_id ?? null;
        $modules['parent_type_id'] = $modules['authLoginUserDetail']?->parent_type_id ?? null;
        $loginUserId = $modules['authLoginUserDetail']?->id ?? null;

        View::share('modules', $modules);

        $import_file = asset('sample-file/ocean-hrms-employee-import-sample.xlsx');

        $company = Company::where('id', $modules['company_id'])->first();
        if ($company && $company->branch_type == 'multiple') {
            $import_file = asset('sample-file/ocean-hrms-employee-import-sample-branch.xlsx');
        }

        return view($modules['folder_path'] . '.import', compact('import_file'));
    }

    public function import_store(Request $request)
    {
        $modules = $this->modules;
        $modules['currentGuard'] = $this->currentGuard ?? null;
        $authLoginUserDetail = $modules['authLoginUserDetail'] = $this->authenticateLoginUserDetails ?? null;
        $company_id = $modules['company_id'] = $authLoginUserDetail?->company_id ?? null;
        $modules['parent_type_id'] = $authLoginUserDetail?->parent_type_id ?? null;
        $loginUserId = $authLoginUserDetail?->id ?? null;

        View::share('modules', $modules);
        View::share('company_id', $company_id);


        $validator = Validator::make($request->all(), [
            'company_id' => ['required', Rule::exists((new Company())->getTable(), 'id')],
            'import_file' => 'required|mimes:xls,xlsx',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {

            if (!empty($modules['company_id']) && $request->company_id != $modules['company_id']) {
                return redirect()->back()->withErrors(['company_id' => 'Unauthorized Company Access'])->withInput();
            }

            $company = Company::findOrFail($request->company_id);
            $company_name = $company->company_name;
            $company_slug = Str::slug($company_name);

            $file = $request->file('import_file');
            $extension = $file->getClientOriginalExtension();
            $filename = now()->format('dmYHis') . rand() . '.' . $extension;

            $year = now()->format('Y');
            $month = now()->format('m');
            $folder = "uploads/{$request->company_id}-{$company_slug}/employee/{$year}-{$month}/import-employee-file";
            $path = $file->storeAs($folder, $filename, 'public');

            $filename_path = "{$folder}/{$filename}";


            $employeeImportFileId = EmployeeImportFile::insertGetId([
                'company_id' => $request->company_id,
                'filename' => $filename_path,
                'total_employees' => 0,
                'total_success_employees' => 0,
                'total_failed_employees' => 0,
                'errors' => '',
                'created_by' => $loginUserId,
                'created_at' => now(),
            ]);


            Excel::import(
                new EmployeeImport($request->company_id, $loginUserId, $modules['currentGuard'], $employeeImportFileId, $authLoginUserDetail),
                $request->file('import_file')
            );

            $employeeImportFile = EmployeeImportFile::find($employeeImportFileId);


            session()->flash('employee_summary', [
                'total_employees' => $employeeImportFile->total_employees,
                'total_success_employees' => $employeeImportFile->total_success_employees,
                'total_failed_employees' => $employeeImportFile->total_failed_employees,
            ]);


            if (!empty($employeeImportFile->errors)) {
                $messages = json_decode($employeeImportFile->errors, true);
                if ($employeeImportFile->total_success_employees > 0) {
                    return redirect()->back()
                        ->with('employeeImportFileMessages', $messages)
                        ->with('success', 'Employees imported successfully with some errors!');
                } else {
                    return redirect()->back()
                        ->with('employeeImportFileMessages', $messages)
                        ->with('error', 'Import completed with errors!');
                }
            }

            return redirect()->back()->with('success', 'Employees imported successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
}
