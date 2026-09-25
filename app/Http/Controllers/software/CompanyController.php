<?php

namespace App\Http\Controllers\software;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\CompanyRequest;
use App\Models\CompanyDetails;
use App\Models\Designation;
use App\Models\DocumentType;
use App\Models\ExpenseCategory;
use App\Models\ExpenseSubCategory;
use App\Models\LeaveType;
use App\Models\MainMenu;
use App\Models\ManageEmail;
use App\Models\RolePermission;
use App\Models\SubMenu;
use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\MasterCountry;
use App\Models\PlanMaster;
use App\Models\TeamRole;
use App\Models\Employee;
use App\Models\MailSetting;
use App\Models\CompanySubscriptionPlan;
use App\Models\CompanySubscriptionAddons;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\DataTables;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\software\CompanySubscriptionPlanController;
use App\Models\AssetsAllocationMaster;
use App\Models\Branch;
use App\Models\EmployeeType;
use App\Models\MasterSetting;
use App\Models\Shift;

class CompanyController extends Controller
{
    public $modules = [];
    public function __construct()
    {
        parent::__construct();

        $this->modules = [
            'title' => 'Company',
            'folder_path' => 'software.modules.company',
            'route' => 'company',
            'table_name' => (new Company())->getTable(),
            'permisstion_prefix' => 'company',
            'module_name' => 'Register Company'
        ];

        $app_right_list = Helper::getMainMenu(['platform' => 'app']);
        $panel_right_list = Helper::getMainMenu(['platform' => 'panel']);

        // dd("L-53", $app_right_list, $panel_right_list);
        View::share('app_right_list', $app_right_list);
        View::share('panel_right_list', $panel_right_list);
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

        try {

            $modules['viewPermission'] = Gate::check('hasPermission', ['view', $modules['module_name']]);
            $modules['addPermission'] = Gate::check('hasPermission', ['add', $modules['module_name']]);
            $modules['editPermission'] = Gate::check('hasPermission', ['update', $modules['module_name']]);

            View::share('modules', $modules);

            if ($request->ajax()) {
                if (!$modules['viewPermission']) {
                    return $this->sendError('Unauthorized', [], [], 403);
                }

                // dd($request->all(), Company::with('plan')->get()->toArray());
                $data = Company::with('plan')
                    ->where(function ($query) {
                        if (Auth::guard('employees')->check()) {
                            $teamPersonCompanyId = Auth::guard('employees')->user()->company_id;
                            $query->where('id', $teamPersonCompanyId);
                        }
                    })
                    ->where(function ($query) use ($request) {
                        if ($request->has('search')) {
                            $query->where('gst_no', 'like', "%" . $request->search . "%");
                            $query->orwhere('app_key', 'like', "%" . $request->search . "%");
                            $query->orwhere('person_name', 'like', "%" . $request->search . "%");
                            $query->orwhere('company_name', 'like', "%" . $request->search . "%");
                            $query->orwhere('whatsapp_number', 'like', "%" . $request->search . "%");
                        }
                    })
                    ->orderBy('id', 'DESC');

                $returnData = Datatables::of($data)
                    ->addIndexColumn()
                    ->filter(function ($query) use ($request) {
                        if ($request->has('filter_plan') && $request->filter_plan) {
                            $query->where('plan_id', $request->filter_plan);
                        }
                        if ($request->has('status') && $request->status !== '' && $request->status !== 'all') {
                            if ($request->status === 'expired') {
                                $query->whereIn('id', function ($sub) {
                                    $sub->selectRaw('company_id')
                                        ->from('company_subscription_plan')
                                        ->where('subscription_status', 'expired')
                                        ->whereRaw('id IN (SELECT MAX(id) FROM company_subscription_plan GROUP BY company_id)');
                                });
                            } else {
                                $query->where('status', $request->status)
                                    ->whereIn('id', function ($sub) {
                                        $sub->selectRaw('company_id')
                                            ->from('company_subscription_plan')
                                            ->where('subscription_status', '!=', 'expired')
                                            ->whereRaw('id IN (SELECT MAX(id) FROM company_subscription_plan GROUP BY company_id)');
                                    });
                            }
                        }
                    })
                    ->setRowClass(function ($row) {
                        $latestPlan = CompanySubscriptionPlan::where('company_id', $row["id"])->where('plan_id', $row->plan_id)->orderBy('id', 'desc')->first();

                        return $latestPlan && $latestPlan?->subscription_status === 'expired' ? 'expired-row' : '';
                    })
                    ->editColumn('created_at', function ($row) {
                        $date = '-';

                        if ($row->created_at) {
                            $date = date('d-m-Y', strtotime($row->created_at));
                        }
                        return $date;
                    })
                    // Company Details
                    ->editColumn('plan_id', function ($row) {
                        return $row->plan->name ?? '-';
                    })
                    // Contact Info
                    ->addColumn('company_details', function ($row) {
                        $html = '<strong>' . e($row->company_name) . '</strong><br>';
                        $html .= e($row->gst_no);
                        return $html;
                    })

                    // Contact Info
                    ->addColumn('contact_info', function ($row) {
                        $html = '';

                        // Person name
                        if (!empty($row->person_name)) {
                            $html .= '<i class="fa fa-user me-1 text-primary"></i>' . $row->person_name;
                        }

                        // WhatsApp number with link
                        if (!empty($row->whatsapp_number)) {
                            $wa_number = preg_replace('/[^0-9]/', '', $row->whatsapp_number);
                            $message = urlencode(""); // Optional: add default message here
                            $wa_link = "https://wa.me/{$wa_number}?text={$message}";
                            $html .= '<br><i class="fab fa-whatsapp text-success me-1"></i> <a href="' . $wa_link . '" target="_blank">' . $row->whatsapp_number . '</a>';
                        }

                        // Email with mailto link
                        if (!empty($row->email)) {
                            $html .= '<br><i class="fa fa-envelope text-danger me-1"></i> <a href="mailto:' . $row->email . '">' . $row->email . '</a>';
                        }

                        return $html;
                    })
                    // Plan & App Key
                    ->addColumn('plan_info', function ($row) {
                        $latestPlan = CompanySubscriptionPlan::where('company_id', $row["id"])->where('plan_id', $row->plan_id)->orderBy('id', 'desc')->first();

                        $totalPurchasePlan = CompanySubscriptionPlan::where('company_id', $row["id"])->count();

                        $planName = $row->plan->name ?? '-';
                        $appKey = $row->app_key ?? '-';

                        $planInfo = '<strong>' . $planName . '</strong><br>' . $appKey . '<br/>';

                        if (!empty($latestPlan?->plan_from)) {
                            $plan_from = \Carbon\Carbon::parse($latestPlan->plan_from)->format('d-m-Y');
                            $planInfo .= '<strong>Plan From:</strong> ' . $plan_from . '<br/>';
                        }

                        if (!empty($latestPlan?->plan_expiry_date)) {
                            $plan_expiry_date = \Carbon\Carbon::parse($latestPlan->plan_expiry_date)->format('d-m-Y');
                            $planInfo .= '<strong>Plan Expiry Date:</strong> ' . $plan_expiry_date . '<br/>';
                        }

                        if (!empty($latestPlan?->subscription_status)) {
                            $planInfo .= '<strong>Subscription Status: ' . ucfirst($latestPlan->subscription_status) . '</strong><br/>';
                        }
                        if ($totalPurchasePlan > 0) {
                            $planInfo .= '<strong>Total Purchase Plan: ' . $totalPurchasePlan . '</strong>';
                        }
                        return $planInfo;
                    })
                    ->editColumn('status', function ($row) use ($modules) {
                        $btn = '';
                        $latestPlan = CompanySubscriptionPlan::where('company_id', $row["id"])->where('plan_id', $row->plan_id)->orderBy('id', 'desc')->first();

                        $dropdown = "";
                        $dropdown .= '<ul class="dropdown-menu" style="">';

                        if (!empty($latestPlan?->subscription_status) && $latestPlan?->subscription_status == 'expired') {
                            $btn .= '<button type="button" class="btn btn-danger btn-sm w-100 waves-effect waves-light">' . ucfirst($latestPlan?->subscription_status) . '</button>';
                        } else {

                            $btnExpired = '<li><a href="javascript:void(0)" class="dropdown-item waves-effect btn-label-dark update-status" data-url="' . route($modules['route'] . '.status-update') . '" data-id="' . $row["id"] . '"  data-update_status="expired">Expire</a></li>';

                            if ($row->status == "active") {
                                $btn .= '<button type="button" class="btn btn-success btn-sm  dropdown-toggle waves-effect waves-light" data-bs-toggle="dropdown" aria-expanded="false">' . ucfirst($row->status) . '</button>';
                                $dropdown .= '<li><a href="javascript:void(0)" class="dropdown-item waves-effect btn-label-danger update-status" data-url="' . route($modules['route'] . '.status-update') . '" data-id="' . $row["id"] . '"  data-update_status="inactive">Inactive</a></li>';

                                $dropdown .= $btnExpired;
                                // $btn = '<span class="badge bg-success bg-glow">Active</span>';
                            } elseif ($row->status == "inactive") {
                                $btn .= '<button type="button" class="btn btn-danger btn-sm  dropdown-toggle waves-effect waves-light" data-bs-toggle="dropdown" aria-expanded="false">' . ucfirst($row->status) . '</button>';
                                $dropdown .= '<li><a href="javascript:void(0)" class="dropdown-item waves-effect btn-label-success update-status" data-url="' . route($modules['route'] . '.status-update') . '" data-id="' . $row["id"] . '"  data-update_status="active">Active</a></li>';
                                // $btn = '<span class="badge bg-danger bg-glow">In-Active</span>';
    
                                $dropdown .= $btnExpired;
                            } else {
                                return $btn;
                            }
                        }
                        $dropdown .= '</ul>';
                        $btn .= $dropdown;
                        return $btn;
                    })
                    ->addColumn('action', function ($row) use ($modules) {
                        $latestPlan = CompanySubscriptionPlan::where('company_id', $row["id"])->where('plan_id', $row->plan_id)->orderBy('id', 'desc')->first();

                        $btn = '';
                        if (!empty($modules['editPermission'])) {
                            $btn .= '<a href="' . route($modules["route"] . ".edit", [$row["id"]]) . '" class="btn btn-light btn-icon mx-1" title="Edit"> <i class="fa-solid fa-pen-to-square"></i> </a>';
                        }

                        $btn .= '
                            <div style="display:inline-block">
                                <button class="btn btn-icon waves-effect waves-light" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="More options">
                                    <i class="ti ti-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu">';
                        $isMasterAdmin = Auth::guard('admin_software')->check() || (Auth::guard('employees')->check() && Auth::guard('employees')->user()->company_id == 1);

                        if ($latestPlan && $latestPlan?->subscription_status === 'expired' && $isMasterAdmin) {
                            $btn .= '<li>
                                <a class="dropdown-item" href="' . route('company.upgrade_plan', [$row["id"]]) . '">
                                    <i class="fa-plus"></i> Upgrade Plan
                                </a>
                            </li>';
                        }


                        $btn .= '<li>
                            <a class="dropdown-item" href="' . route('company.mail_setting', [$row["id"]]) . '">
                                <i class="tf-icons ti ti-mail-cog"></i> Mail Configration
                            </a>
                        </li>';

                        if ($isMasterAdmin) {
                            $btn .= '<li>
                                <a class="dropdown-item" href="' . route('company.license_setting', [$row["id"]]) . '">
                                    <i class="tf-icons ti ti-settings"></i> License Setting
                                </a>
                            </li>';
                        }

                        $btn .= '<li>
                            <a class="dropdown-item" href="' . route('company-subscription-plan.show', [$row["id"]]) . '">
                                <i class="tf-icons ti ti-history"></i> Subscription History
                            </a>
                        </li>';
                        if ($isMasterAdmin) {
                            $btn .= '<li>
                                <a class="dropdown-item deletebutton text-danger" href="javascript:void(0)" data-id="' . $row->id . '" data-did="' . route($modules["route"] . ".destroy", [$row["id"]]) . '">
                                    <i class="tf-icons ti ti-trash"></i> Delete Company & Related Data
                                </a>
                            </li>';
                        }
                        $btn .= '
                                </ul>
                            </div>
                        ';

                        if (($btn) === '') {
                            $btn = '-';
                        }

                        return $btn;
                    })

                    ->rawColumns(['status', 'action', 'company_details', 'contact_info', 'plan_info'])
                    ->make(true);
                return $returnData;
            }

            if (!$modules['viewPermission']) {
                abort(403, 'Unauthorized');
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
        try {
            $modules['addPermission'] = Gate::check('hasPermission', ['add', $modules['module_name']]);
            if (!$modules['addPermission']) {
                abort(403, 'Unauthorized');
            }

            $plans = PlanMaster::where('status', 'active')->get();
            View::share('modules', $modules);
            View::share('plans', $plans);
            View::share('dateFormatOptions', Helper::getSupportedDateFormats());
            View::share('timeFormatOptions', Helper::getSupportedTimeFormats());

            return view($modules['folder_path'] . '.add');
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CompanyRequest $request)
    {
        $modules = $this->modules;
        $modules['authLoginUserDetail'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails : null;
        $modules['company_id'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails?->company_id : null;
        $modules['parent_type_id'] = ($this->authenticateLoginUserDetails?->parent_type_id) ? $this->authenticateLoginUserDetails?->parent_type_id : null;
        $loginUserId = ($modules['authLoginUserDetail'] && $modules['authLoginUserDetail']?->id) ? $modules['authLoginUserDetail']?->id : null;
        View::share('modules', $modules);
        // dd("L-353",$request->all());

        $validated = $request->validated();
        $providedHra = $request->input('hra_percentage');
        $validated['hra_percentage'] = ($providedHra !== null && $providedHra !== '') ? $providedHra : 40;
        $validated['date_format'] = $request->input('date_format') ?: Helper::getDefaultDateFormat();
        $validated['time_format'] = $request->input('time_format') ?: Helper::getDefaultTimeFormat();
        $validated['employee_code_auto_generation'] = $request->input('employee_code_auto_generation') ?: 'auto';
        $input = $request->all();

        DB::beginTransaction();
        try {
            // return $validated;
            $validated['created_by'] = $loginUserId;
            $validated['sp'] = Helper::generateSP($request->password);

            $plan_data = PlanMaster::where('id', $request->plan_id)->first();
            $plan_from = date('Y-m-d');
            $plan_to = date('Y-m-d');


            if (!empty($plan_data->plan_valid_day)) {
                $plan_to = Carbon::now()->addDays((int) $plan_data->plan_valid_day)->format('Y-m-d');
            }

            $plan_to = $plan_to;
            $validated['max_employee_user_count'] = $plan_data->max_employee_user_count;

            $country = MasterCountry::where('id', $request->country_id)->first();
            $validated['phonecode'] = $country->code;

            $validated['app_right'] = $plan_data->app_right;
            $validated['panel_right'] = $plan_data->panel_right;
            $validated['panel_url'] = url('/software/login');

            $defaultValues = [
                'mobile_min' => 10,
                'mobile_max' => 10,
                'branch_type' => 'single',
                'status' => 'active',
            ];

            $validated = array_merge($validated, $defaultValues);
            $validated['sp'] = Helper::generateSP($request->password);
            $validated['password'] = Hash::make($validated['password']);
            $companyName = trim($request->company_name);
            $cleanName = preg_replace('/[^A-Za-z0-9]/', '', $companyName);
            if (strlen($cleanName) < 3) {
                $cleanName = str_pad($cleanName, 3, 'X');
            }
            $prefix = ucfirst(strtolower(substr($cleanName, 0, 3)));
            $validated['app_key'] = $prefix . "@" . date("Y");
            // app_key
            // dd("L-395",$validated, Helper::getCurrentGuard());
            $company = Company::create($validated);
            $company_id = $company->id;

            CompanyDetails::create([
                'company_id' => $company_id,
            ]);

            $team_role = TeamRole::create([
                'company_id' => $company_id,
                'parent_id' => '0',
                'name' => $request->company_name,
                'created_type' => Helper::getCurrentGuard(),
                'created_by' => $validated['created_by'],
                // 'updated_by' => $validated['created_by'],
            ]);
            // dd("L-434", $team_role->toArray());

            // Default Team Role Hierarchy: Main HR -> Department Head -> Supervisor -> Employee
            $mainHrRole = TeamRole::create([
                'company_id' => $company_id,
                'parent_id' => $team_role->id,
                'name' => 'Main HR',
                'status' => 'active',
                'created_type' => Helper::getCurrentGuard(),
                'created_by' => $validated['created_by'],
            ]);

            $deptHeadRole = TeamRole::create([
                'company_id' => $company_id,
                'parent_id' => $mainHrRole->id,
                'name' => 'Department Head',
                'status' => 'active',
                'created_type' => Helper::getCurrentGuard(),
                'created_by' => $validated['created_by'],
            ]);

            $supervisorRole = TeamRole::create([
                'company_id' => $company_id,
                'parent_id' => $deptHeadRole->id,
                'name' => 'Supervisor',
                'status' => 'active',
                'created_type' => Helper::getCurrentGuard(),
                'created_by' => $validated['created_by'],
            ]);

            $employeeRole = TeamRole::create([
                'company_id' => $company_id,
                'parent_id' => $supervisorRole->id,
                'name' => 'Employee',
                'status' => 'active',
                'created_type' => Helper::getCurrentGuard(),
                'created_by' => $validated['created_by'],
            ]);

            $panelRights = $plan_data->panel_right;

            $mainMenus = MainMenu::where('status', 'active')->get();

            foreach ($mainMenus as $mainMenu) {
                $subMenus = SubMenu::where('main_menu_id', $mainMenu->id)
                    ->where('status', 'active')
                    ->get();

                foreach ($subMenus as $subMenu) {
                    if (in_array((string) $subMenu->id, $panelRights)) {  // Match by submenu ID
                        RolePermission::create([
                            'company_id' => $company_id,
                            'team_role_id' => $team_role->id,
                            'main_menu_id' => $mainMenu->id,
                            'sub_menu_id' => $subMenu->id,
                            'view_flag' => 1,
                            'add_flag' => 1,
                            'update_flag' => 1,
                            'delete_flag' => 1,
                            'restore_flag' => 1,
                            'print_flag' => 1,
                            'excel_flag' => 1,
                            'approval_flag' => 1,
                            'all_data_flag' => 1,
                            'status' => 'active',
                            'created_by' => $validated['created_by'],
                        ]);
                    }
                }
            }


            // Insert team person
            $defulatEmployeeCreate = [
                'company_id' => $company_id,
                'branch_id' => 0,
                'parent_id' => 0,
                'first_name' => $request->person_name,
                'full_name' => $request->person_name,

                'username' => $request->whatsapp_number,
                'password' => Hash::make($request->password),
                'sp' => Helper::generateSP($request->password),

                'country_id' => $request->country_id,
                'state_id' => $request->state_id,
                'city_id' => $request->city_id,
                'email' => $request->email,
                'contact_number' => $request->whatsapp_number,
                'other_number' => $request->whatsapp_number,

                'name' => $request->person_name,
                'parent_type_id' => 0,
                'mobile_no' => $request->whatsapp_number,
                'employee_code' => $this->generateEmployeeCode($company_id),

                'role_id' => $team_role->id,

                'created_by' => $validated['created_by'],
                // 'updated_by' => $validated['created_by'],
            ];
            // dd($defulatEmployeeCreate);
            $teamPerson = Employee::create($defulatEmployeeCreate);

            // Designations
            $defaultDesignations = ['Admin', 'Main HR', 'Department Head', 'Supervisor', 'Employee'];
            foreach ($defaultDesignations as $designationName) {
                Designation::create([
                    'company_id' => $company_id,
                    'name' => $designationName,
                    'status' => 'active',
                    'created_by' => $validated['created_by'],
                ]);
            }

            // Document Type
            DocumentType::create([
                'company_id' => $company_id,
                'name' => 'General Documents',
                'status' => 'active',
                'created_by' => $validated['created_by'],
            ]);


            // Leave Type
            LeaveType::create([
                'company_id' => $company_id,
                'sort_name' => 'SL',
                'full_name' => "Sick Leave",
                'count' => 12,
                'mode' => 0,
                'status' => 'active',
                'created_by' => $validated['created_by'],
            ]);

            LeaveType::create([
                'company_id' => $company_id,
                'sort_name' => 'CL',
                'full_name' => "Casual Leave",
                'count' => 12,
                'mode' => 0,
                'status' => 'active',
                'created_by' => $validated['created_by'],
            ]);

            // Default Employee Types
            EmployeeType::firstOrCreate(
                ['company_id' => $company_id, 'name' => 'Company Payroll'],
                ['status' => 'active', 'created_by' => $validated['created_by']]
            );
            EmployeeType::firstOrCreate(
                ['company_id' => $company_id, 'name' => 'Contractor Salary'],
                ['status' => 'active', 'created_by' => $validated['created_by']]
            );

            /*
            // Expense Category
            $expenseCategory = ExpenseCategory::create([
                'company_id' => $company_id,
                'name' => 'General Expense Category',
                'status' => 'active',
                'created_by' => $validated['created_by'],
            ]);

            // Expense SubCategory
            ExpenseSubCategory::create([
                'company_id' => $company_id,
                'expense_category_id' => $expenseCategory->id,
                'team_person_ids' => (string) $teamPerson->id,
                'name' => 'General Expense Sub Category',
                'expense_type' => 'General',
                'is_image_required' => 0,
                'min_amount' => 1,
                'max_amount' => 10000,
                'per_km_rate' => null,
                'fix_amount' => null,
                'from_time' => null,
                'to_time' => null,
                'status' => 'active',
                'created_by' => $validated['created_by'],
            ]);
            */

            /*
            ManageEmail::create([
                'company_id' => $company_id,
                'module' => 'Setting',
                'ntype' => 'forgot_password',
                'name' => 'Reset Password',
                'subject' => 'Reset Password',
                'body' => '<figure class="table">
                <table>
                    <tbody>
                        <tr>
                            <td style="padding:30px;text-align:center;">
                                <h2 style="margin-left:0;">Hello !</h2>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:30px;">
                                <p>You are receiving this email because we received a password reset request for your <strong>Ocean CRM</strong> account.</p>
                                <p style="margin-left:0;text-align:center;">[link]</p>
                                <p>This password reset link will expire in <strong>60 minutes</strong>.</p>
                                <p>If you did not request a password reset, no further action is required.</p>
                                <p>Regards,&nbsp;<br><strong>Ocean CRM Team</strong></p>
                            </td>
                        </tr>
                        <tr>
                            <td style="background-color:#f1f1f1;padding:15px;text-align:center;">
                                © ' . date('Y') . ' Ocean CRM. All rights reserved.
                            </td>
                        </tr>
                    </tbody>
                </table>
                </figure>',
                'status' => 'active',
                'created_by' => $validated['created_by'],
                'updated_by' => $validated['created_by'],
            ]);
            */


            $today = Carbon::today();
            $subscription_status = '';
            if ($plan_from && $plan_to) {
                if ($today->between(Carbon::parse($plan_from), Carbon::parse($plan_to))) {
                    $subscription_status = 'active';
                } else {
                    $subscription_status = 'expired';
                }
            }

            CompanySubscriptionPlan::create([
                'company_id' => $company_id,
                'plan_id' => $request->plan_id,
                'plan_from' => $plan_from,
                'plan_to' => $plan_to,
                'extra_detail' => json_encode($plan_data->toArray()),
                'plan_expiry_date' => $plan_to,
                'subscription_status' => $subscription_status,
                'created_by' => $loginUserId,
            ]);

            DB::commit();
            return $this->sendResponse([], "Company created successfully.");
            //return response()->json([
            //    'status' => true,
            //    'message' => $modules['title'] . ' created successfully'
            //], 200);
            //return Redirect::route($modules['route'] . '.index')->withSuccess($modules['title'] . ' create successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            // return $e->getMessage();
            //return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
            Log::error('Error occurred while creating company: ' . $e->getMessage());
            return $this->sendError($e->getMessage());
            //return response()->json([
            //    'status' => false,
            //    'message' => 'Error occurred while creating company: ' . $e->getMessage()
            //], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        if ($id = "print") {
            // return self::print();
        }
        dd($id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $isMasterAdmin = Auth::guard('admin_software')->check() || (Auth::guard('employees')->check() && Auth::guard('employees')->user()->company_id == 1);
        if (!$isMasterAdmin && Auth::guard('employees')->check()) {
            $loggedInCompanyId = Auth::guard('employees')->user()->company_id;
            if ((int)$id !== (int)$loggedInCompanyId) {
                abort(404);
            }
        }

        $modules = $this->modules;
        $modules['currentGuard'] = ($this->currentGuard) ? $this->currentGuard : null;
        $modules['authLoginUserDetail'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails : null;
        $modules['company_id'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails?->company_id : null;
        $modules['parent_type_id'] = ($this->authenticateLoginUserDetails?->parent_type_id) ? $this->authenticateLoginUserDetails?->parent_type_id : null;
        $loginUserId = ($modules['authLoginUserDetail'] && $modules['authLoginUserDetail']?->id) ? $modules['authLoginUserDetail']?->id : null;

        try {
            $modules['updatePermission'] = Gate::check('hasPermission', ['update', $modules['module_name']]);
            if (!$modules['updatePermission']) {
                abort(403, 'Unauthorized');
            }

            $modules['loginType'] = isset($modules['currentGuard']) ? $modules['currentGuard'] : null;

            // return $modules;

            $plans = PlanMaster::where('status', 'active')->get();
            $countries = MasterCountry::where('status', 'active')->get();
            View::share('plans', $plans);

            View::share('countries', $countries);
            View::share('dateFormatOptions', Helper::getSupportedDateFormats());
            View::share('timeFormatOptions', Helper::getSupportedTimeFormats());

            $edit = Company::with(['company_details'])->findOrFail($id);
            // dd($edit);
            View::share('edit', $edit);

            View::share('modules', $modules);

            return view($modules['folder_path'] . '.edit');
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CompanyRequest $request, string $id)
    {
        $isMasterAdmin = Auth::guard('admin_software')->check() || (Auth::guard('employees')->check() && Auth::guard('employees')->user()->company_id == 1);
        if (!$isMasterAdmin && Auth::guard('employees')->check()) {
            $loggedInCompanyId = Auth::guard('employees')->user()->company_id;
            if ((int)$id !== (int)$loggedInCompanyId) {
                abort(404);
            }
        }

        if (!$this->checkCompanyDetailFullAccess()) {
            abort(403, 'Unauthorized action. Only authorized roles can update organization details.');
        }

        $modules = $this->modules;
        $modules['authLoginUserDetail'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails : null;
        $modules['company_id'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails?->company_id : null;
        $modules['parent_type_id'] = ($this->authenticateLoginUserDetails?->parent_type_id) ? $this->authenticateLoginUserDetails?->parent_type_id : null;
        $loginUserId = ($modules['authLoginUserDetail'] && $modules['authLoginUserDetail']?->id) ? $modules['authLoginUserDetail']?->id : null;

        // return $request->all();

        $request['id'] = $id;

        $validated = $request->validated();
        $providedHra = $request->input('hra_percentage');
        $validated['hra_percentage'] = ($providedHra !== null && $providedHra !== '') ? $providedHra : 40;
        $validated['date_format'] = $request->input('date_format') ?: Helper::getDefaultDateFormat();
        $validated['time_format'] = $request->input('time_format') ?: Helper::getDefaultTimeFormat();
        $input = $request->all();
        $input['hra_percentage'] = $validated['hra_percentage'];
        $input['date_format'] = $validated['date_format'];
        $input['time_format'] = $validated['time_format'];
        // return $validated;
        try {
            $validated['updated_by'] = $loginUserId;

            $updateData = Company::findOrFail($id);

            // dd($validated, $request->all());
            //$updateData->update($validated);

            CompanyDetails::updateOrCreate(
                ['company_id' => $updateData->id],
                [],
            );

            $request['is_copyright_view'] = $request->is_copyright_view ? 'yes' : 'no';

            $sub_folder_path = Helper::fileUploadPath($modules['authLoginUserDetail'], Company::$folderPath, $id);
            $company_name = $request?->company_name ?? 'default-company';

            if ($request->hasFile('company_logo')) {

                if ($updateData?->company_logo && file_exists($updateData?->company_logo)) {
                    unlink($updateData?->company_logo);
                }
                ;

                $image_name = Helper::make_slug('company_logo ' . (string) $loginUserId . ' ' . $company_name . ' ' . date('Ymd-His'));

                $file = $request->file('company_logo');

                $extenstion = $file->getClientOriginalExtension();

                $filename = $image_name . '.' . $extenstion;
                $uploadedPath = public_path($sub_folder_path);

                if (!file_exists($uploadedPath)) {
                    mkdir($uploadedPath, 0777, true);
                }
                // return $uploadedPath;
                if ($file->move($uploadedPath, $filename)) {
                    $uploadedImage = $sub_folder_path . $filename;
                    if (in_array($extenstion, ["jpeg", "png", "jpg"])) {
                        $webP = Helper::existingImageConverToWebp($extenstion, $uploadedPath, $filename, $image_name, 60, true);
                        if ($webP) {
                            $uploadedImage = $sub_folder_path . $webP;
                            $extenstion = "webp";
                        }
                    }
                    // return $uploadedImage;
                    $input['company_logo'] = $uploadedImage;
                }
            }

            if ($request->hasFile('white_labeling_logo')) {
                if ($updateData?->white_labeling_logo && file_exists($updateData?->white_labeling_logo)) {
                    unlink($updateData?->white_labeling_logo);
                }
                ;

                $image_name = Helper::make_slug('white_labeling_logo ' . (string) $loginUserId . ' ' . $company_name . ' ' . date('Ymd-His'));

                $file = $request->file('white_labeling_logo');

                $extenstion = $file->getClientOriginalExtension();

                $filename = $image_name . '.' . $extenstion;

                $uploadedPath = public_path($sub_folder_path);
                // return $uploadedPath;
                if ($file->move($uploadedPath, $filename)) {
                    $uploadedImage = $sub_folder_path . $filename;
                    if (in_array($extenstion, ["jpeg", "png", "jpg"])) {
                        $webP = Helper::existingImageConverToWebp($extenstion, $uploadedPath, $filename, $image_name, 60, true);
                        if ($webP) {
                            $uploadedImage = $sub_folder_path . $webP;
                            $extenstion = "webp";
                        }
                    }
                    // return $uploadedImage;
                    $input['white_labeling_logo'] = $uploadedImage;
                }
            }

            if ($request->hasFile('company_favicon')) {
                if ($updateData?->company_favicon && file_exists($updateData?->company_favicon)) {
                    unlink($updateData?->company_favicon);
                }
                ;

                $image_name = Helper::make_slug('company_favicon ' . (string) $loginUserId . ' ' . $company_name . ' ' . date('Ymd-His'));

                $file = $request->file('company_favicon');

                $extenstion = $file->getClientOriginalExtension();

                $filename = $image_name . '.' . $extenstion;

                $uploadedPath = public_path($sub_folder_path);
                // return $uploadedPath;
                if ($file->move($uploadedPath, $filename)) {
                    $uploadedImage = $sub_folder_path . $filename;
                    if (in_array($extenstion, ["jpeg", "png", "jpg"])) {
                        $webP = Helper::existingImageConverToWebp($extenstion, $uploadedPath, $filename, $image_name, 60, true);
                        if ($webP) {
                            $uploadedImage = $sub_folder_path . $webP;
                            $extenstion = "webp";
                        }
                    }
                    // return $uploadedImage;
                    $input['company_favicon'] = $uploadedImage;
                }
            }

            if ($request->hasFile('watermark_logo')) {
                if ($updateData?->watermark_logo && file_exists($updateData?->watermark_logo)) {
                    unlink($updateData?->watermark_logo);
                }

                $image_name = Helper::make_slug('watermark_logo ' . (string) $loginUserId . ' ' . $company_name . ' ' . date('Ymd-His'));

                $file = $request->file('watermark_logo');

                $extenstion = $file->getClientOriginalExtension();

                $filename = $image_name . '.' . $extenstion;

                $uploadedPath = public_path($sub_folder_path);

                if (!file_exists($uploadedPath)) {
                    mkdir($uploadedPath, 0777, true);
                }

                if ($file->move($uploadedPath, $filename)) {
                    $uploadedImage = $sub_folder_path . $filename;
                    if (in_array($extenstion, ["jpeg", "png", "jpg"])) {
                        $webP = Helper::existingImageConverToWebp($extenstion, $uploadedPath, $filename, $image_name, 60, true);
                        if ($webP) {
                            $uploadedImage = $sub_folder_path . $webP;
                            $extenstion = "webp";
                        }
                    }
                    $input['watermark_logo'] = $uploadedImage;
                }
            }

            if ($request->hasFile('app_logo')) {
                if ($updateData?->app_logo && file_exists($updateData?->app_logo)) {
                    unlink($updateData?->app_logo);
                }
                ;

                $image_name = Helper::make_slug('app_logo ' . (string) $loginUserId . ' ' . $company_name . ' ' . date('Ymd-His'));

                $file = $request->file('app_logo');

                $extenstion = $file->getClientOriginalExtension();

                $filename = $image_name . '.' . $extenstion;

                $uploadedPath = public_path($sub_folder_path);
                // return $uploadedPath;
                if ($file->move($uploadedPath, $filename)) {
                    $uploadedImage = $sub_folder_path . $filename;
                    if (in_array($extenstion, ["jpeg", "png", "jpg"])) {
                        $webP = Helper::existingImageConverToWebp($extenstion, $uploadedPath, $filename, $image_name, 60, true);
                        if ($webP) {
                            $uploadedImage = $sub_folder_path . $webP;
                            $extenstion = "webp";
                        }
                    }
                    // return $uploadedImage;
                    $input['app_logo'] = $uploadedImage;
                }
            }

            if ($request->hasFile('header_image')) {

                if ($updateData?->header_image && file_exists($updateData?->header_image)) {
                    unlink($updateData?->header_image);
                }
                ;

                $image_name = Helper::make_slug('header_image ' . (string) $loginUserId . ' ' . $company_name . ' ' . date('Ymd-His'));

                $file = $request->file('header_image');

                $extenstion = $file->getClientOriginalExtension();

                $filename = $image_name . '.' . $extenstion;

                $uploadedPath = public_path($sub_folder_path);
                // return $uploadedPath;
                if ($file->move($uploadedPath, $filename)) {
                    $uploadedImage = $sub_folder_path . $filename;
                    if (in_array($extenstion, ["jpeg", "png", "jpg"])) {
                        $webP = Helper::existingImageConverToWebp($extenstion, $uploadedPath, $filename, $image_name, 60, true);
                        if ($webP) {
                            $uploadedImage = $sub_folder_path . $webP;
                            $extenstion = "webp";
                        }
                    }
                    // return $uploadedImage;
                    $input['order_header_logo'] = $uploadedImage;
                }
            }

            if ($request->hasFile('footer_image')) {


                if ($updateData?->footer_image && file_exists($updateData?->footer_image)) {
                    unlink($updateData?->footer_image);
                }
                ;

                $image_name = Helper::make_slug('footer_image ' . (string) $loginUserId . ' ' . $company_name . ' ' . date('Ymd-His'));

                $file = $request->file('footer_image');

                $extenstion = $file->getClientOriginalExtension();

                $filename = $image_name . '.' . $extenstion;

                $uploadedPath = public_path($sub_folder_path);
                // return $uploadedPath;
                if ($file->move($uploadedPath, $filename)) {
                    $uploadedImage = $sub_folder_path . $filename;
                    if (in_array($extenstion, ["jpeg", "png", "jpg"])) {
                        $webP = Helper::existingImageConverToWebp($extenstion, $uploadedPath, $filename, $image_name, 60, true);
                        if ($webP) {
                            $uploadedImage = $sub_folder_path . $webP;
                            $extenstion = "webp";
                        }
                    }
                    // return $uploadedImage;
                    $input['order_footer_logo'] = $uploadedImage;
                }
            }

            if ($request->hasFile('handbook_file')) {
                $file = $request->file('handbook_file');
                $ext = strtolower($file->getClientOriginalExtension());
                $compSlug = \Illuminate\Support\Str::slug($updateData->id . ' ' . $updateData->company_name);
                $handbookFolder = "uploads/" . $compSlug . "/handbook/";
                $handbookDir = public_path($handbookFolder);
                if (!file_exists($handbookDir)) {
                    mkdir($handbookDir, 0777, true);
                }

                // Remove existing old handbook files
                foreach (['handbook.pdf', 'handbook.webp', 'handbook.png', 'handbook.jpg', 'handbook.jpeg'] as $oldF) {
                    if (file_exists($handbookDir . $oldF)) {
                        @unlink($handbookDir . $oldF);
                    }
                }

                if ($ext === 'pdf') {
                    $file->move($handbookDir, 'handbook.pdf');
                } elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                    $tempPath = $file->getPathname();
                    $imageContent = file_get_contents($tempPath);
                    $img = @imagecreatefromstring($imageContent);
                    if ($img !== false) {
                        imagepalettetotruecolor($img);
                        imagealphablending($img, true);
                        imagesavealpha($img, true);
                        imagewebp($img, $handbookDir . 'handbook.webp', 80);
                        imagedestroy($img);
                    } else {
                        $file->move($handbookDir, 'handbook.' . $ext);
                    }
                } else {
                    $file->move($handbookDir, 'handbook.' . $ext);
                }
            }

            // dd("L-711", $request->all(), $input);
            if ($updateData) {
                unset($validated['id']);

                $colorFields = [
                    "status_bar_color",
                    "title_name_color",
                    "all_icon_color",
                    "edittext_title_color",
                    "screen_background_light_color",
                    "screen_background_dark_color",
                    "all_screen_header_color",
                    "all_screen_back_arrow_background_color",
                    "all_screen_back_arrow_color",
                    "data_list_border_color",
                    "login_text_color_1",
                    "login_text_color_2",
                    "background_shape_1",
                    "background_shape_2",
                    "background_shape_3",
                    "extra_color_1",
                    "extra_color_2",
                    "extra_color_3",
                ];

                // Separate color fields from the main input
                $companyDetailData = [];
                foreach ($colorFields as $field) {
                    if (array_key_exists($field, $input)) {
                        $companyDetailData[$field] = $input[$field];
                        unset($input[$field]); // remove from input to avoid updating Company with these
                    }
                }

                // Update company record (main company table)
                $updateData->update($input);

                // Update or create related company detail record
                if (!empty($companyDetailData)) {
                    CompanyDetails::updateOrCreate(
                        ['company_id' => $updateData->id],
                        $companyDetailData
                    );
                }

                if (!empty($request?->team_set) && $request?->team_set === 'team_update' && !empty($request?->tab && $request?->tab === 'profile-tab')) {
                    return Redirect::route($modules['route'] . '.detail', ['id' => $id, 'tab' => 'profile-tab'])->withSuccess('Company update successfully');
                } else {
                    return Redirect::route($modules['route'] . '.index')->withSuccess($modules['title'] . ' update successfully');
                }
            }
            return Redirect::back()->withErrors('something went wrong please try again later')->withInput();
        } catch (\Exception $e) {
            return $e->getMessage();
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        $modules = $this->modules;
        $modules['authLoginUserDetail'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails : null;
        $modules['company_id'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails?->company_id : null;
        $modules['parent_type_id'] = ($this->authenticateLoginUserDetails?->parent_type_id) ? $this->authenticateLoginUserDetails?->parent_type_id : null;
        $loginUserId = ($modules['authLoginUserDetail'] && $modules['authLoginUserDetail']?->id) ? $modules['authLoginUserDetail']?->id : null;

        $isAjax = ($request->ajax()) ? true : false;
        if (Auth::guard('admin_software')->check() && Auth::guard('admin_software')->id() == 1) {
            DB::beginTransaction();
            $dataDelete = Company::findOrFail($id);
            try {
                if ($dataDelete) {

                    /*
                    Branch::where('company_id', $id)->update([
                        'deleted_by' => $loginUserId,
                        'deleted_at' => Helper::trait_current_date(),
                    ]);

                    Shift::where('company_id', $id)->update([
                        'deleted_by' => $loginUserId,
                        'deleted_at' => Helper::trait_current_date(),
                    ]);

                    LeaveType::where('company_id', $id)->update([
                        'deleted_by' => $loginUserId,
                        'deleted_at' => Helper::trait_current_date(),
                    ]);

                    EmployeeType::where('company_id', $id)->update([
                        'deleted_by' => $loginUserId,
                        'deleted_at' => Helper::trait_current_date(),
                    ]);

                    Designation::where('company_id', $id)->update([
                        'deleted_by' => $loginUserId,
                        'deleted_at' => Helper::trait_current_date(),
                    ]);

                    AssetsAllocationMaster::where('company_id', $id)->update([
                        'deleted_by' => $loginUserId,
                        'deleted_at' => Helper::trait_current_date(),
                    ]);
                    */

                    $validated['deleted_by'] = $loginUserId;
                    $dataDelete->update($validated);

                    if ($dataDelete->delete()) {
                        if ($isAjax) {
                            DB::commit();
                            return $this->sendResponse([], $modules['title'] . ' delete successfully');
                        }
                        DB::commit();
                        return true;
                    }
                }
                if ($isAjax) {
                    DB::rollBack();
                    return $this->sendResponse([], "something went wrong please try again later");
                }
                DB::rollBack();
                return false;
            } catch (\Exception $e) {
                DB::rollBack();
                return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
            }
        }
        if ($isAjax) {
            return $this->sendError('You can not access for this action.', [], [], 403);
        }
        return Redirect::route($modules['route'] . '.index')->withErrors(['You can not access for this action.']);
    }

    public function check_company_exists(Request $request)
    {
        $company_name = $request->company_name;
        $gst_no = $request->gst_no;
        $whatsapp_number = $request->whatsapp_number;
        $email = $request->email;

        if ($company_name) {
            $company = Company::where('company_name', $company_name)->first();
            if ($company) {
                return response()->json([
                    'status' => false,
                    'message' => 'Company name is already registered.'
                ], 200);
            }
        }

        if ($gst_no) {
            $gstCompany = Company::where('gst_no', $gst_no)->first();
            if ($gstCompany) {
                return response()->json([
                    'status' => false,
                    'message' => 'GST number is already registered.'
                ], 200);
            }
        }

        if ($whatsapp_number) {
            $phoneCompany = Company::where('whatsapp_number', $whatsapp_number)->first();
            if ($phoneCompany) {
                return response()->json([
                    'status' => false,
                    'message' => 'WhatsApp number is already registered with another company.'
                ], 200);
            }
            $empUser = Employee::where('username', $whatsapp_number)->first();
            if ($empUser) {
                return response()->json([
                    'status' => false,
                    'message' => 'WhatsApp number is already in use as a login username.'
                ], 200);
            }
        }

        if ($email) {
            $emailCompany = Company::where('email', $email)->first();
            if ($emailCompany) {
                return response()->json([
                    'status' => false,
                    'message' => 'Email address is already registered with another company.'
                ], 200);
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Validation passed.'
        ], 200);
    }

    function generateEmployeeCode($companyId)
    {
        $lastEmployee = Employee::where('company_id', $companyId)->orderBy('id', 'desc')->first();
        $lastCode = $lastEmployee ? intval(str_replace('EMP-', '', $lastEmployee->employee_code)) : 0;
        $nextNumber = $lastCode + 1;
        $employeeCode = 'EMP-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        return $employeeCode;
    }

    public function status_update(Request $request)
    {
        $isAjax = ($request->ajax()) ? true : false;

        $modules = $this->modules;
        $validator = Validator::make($request->all(), [
            'id' => ['required', Rule::exists($modules['table_name'], 'id')],
            'update_status' => ['required', 'in:active,inactive,expired']
        ]);

        if ($validator->fails() && $isAjax) {
            return $this->sendError($validator->messages()->first(), $validator->messages(), [], 401);
        }


        try {
            $country = Company::withTrashed()->findOrFail($request?->id);
            if ($country) {
                if ($request->update_status == 'expired') {
                    $companySubscriptionPlan = CompanySubscriptionPlan::where('company_id', $country->id)->where('plan_id', $country->plan_id)->where('subscription_status', 'active')->orderBy('id', 'desc')->first();
                    if ($companySubscriptionPlan) {
                        $companySubscriptionPlan->subscription_status = 'expired';
                        $companySubscriptionPlan->plan_expiry_date = date('Y-m-d');
                        $companySubscriptionPlan->platform = 'manually';
                        $companySubscriptionPlan->save();
                    }
                } else {
                    $country->status = $request->update_status;
                    $country->save();
                }

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

    public function set_master_config(Request $request, $platform, $id = null)
    {

        $modules = $this->modules;
        $modules['authLoginUserDetail'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails : null;
        $modules['company_id'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails?->company_id : null;
        $modules['parent_type_id'] = ($this->authenticateLoginUserDetails?->parent_type_id) ? $this->authenticateLoginUserDetails?->parent_type_id : null;
        $modules['currentGuard'] = ($this->currentGuard) ? $this->currentGuard : null;
        $loginUserId = ($modules['authLoginUserDetail'] && $modules['authLoginUserDetail']?->id) ? $modules['authLoginUserDetail']?->id : null;

        $modules['title'] = ucfirst($platform) . " - Config";
        View::share('modules', $modules);

        if (isset($modules['currentGuard']) && $modules['currentGuard'] != 'admin_software') {
            if (isset($modules['company_id']) && $modules['company_id']) {
                $id = $modules['company_id'];
            }
        } else {
            $id = $request?->company_id;
        }

        try {

            View::share('platform', $platform);
            if ($request?->_token) {
                if (!isset($id) || empty($id) || (int) $id <= 0) {
                    return back()->withInput()->withErrors(["Please Select Company."]);
                }

                // Define validation rules
                $rules = [
                    'company_id' => ['required', 'integer', 'exists:' . (new Company())->getTable() . ',id'],
                    'facebook_app_id' => ['required'],
                    'facebook_app_secret' => ['required'],
                    'facebook_redirect_url' => ['required', 'url'],
                ];

                // Run the validator
                $validator = Validator::make($request->all(), $rules);

                if ($validator->fails()) {
                    return back()->withErrors($validator)->withInput();
                }

                $updateData = MasterSetting::where('company_id', $id)->where('platform', $request?->platform)->first();
                if (!$updateData) {
                    $updateData = new MasterSetting();
                    $updateData->company_id = $id;
                    $updateData->platform = $request?->platform;
                }
                $updateData->app_id = $request[$request?->platform . '_app_id' ?? null];
                $updateData->app_secret = $request[$request?->platform . '_app_secret' ?? null];
                $updateData->redirect_url = $request[$request?->platform . '_redirect_url' ?? null];
                $updateData->save();


                return Redirect::back()->withSuccess(ucfirst($request?->platform) . ' Settings successfully.');
            }
            $company = Company::find($id);
            View::share('company', $company);

            return view($modules['folder_path'] . '.master_setting');
        } catch (\Exception $e) {
            return $e->getMessage();
        } catch (\Throwable $th) {
            return $th->getMessage();
            //throw $th;
        }
    }

    public function set_company_session(Request $request)
    {
        $modules = $this->modules;
        $modules['authLoginUserDetail'] = $this->authenticateLoginUserDetails ?? null;
        $modules['company_id'] = $modules['authLoginUserDetail']?->company_id;
        $modules['parent_type_id'] = $modules['authLoginUserDetail']?->parent_type_id;
        $loginUserId = $modules['authLoginUserDetail']?->id;

        try {
            $companyId = $request->company_id;

            // Allow null/empty for "All Companies" selection
            if (!empty($companyId)) {
                $validator = Validator::make($request->all(), [
                    'company_id' => ['required', Rule::exists((new Company())->getTable(), 'id')],
                ]);

                if ($validator->fails()) {
                    return $this->sendError($validator->messages()->first(), $validator->messages(), [], 401);
                }
            }

            // Set or clear the session
            if (!empty($companyId)) {
                session(['selected_company_id' => $companyId]);
            } else {
                session()->forget('selected_company_id');
            }

            return $this->sendResponse([
                'selected_company_id' => $companyId,
                'status' => true
            ], 'Company selection updated successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->sendError('Validation failed.', $e->errors(), [], 422);
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong', $e->getMessage(), [], 500);
        }
    }

    public function verify_website_api_code(Request $request)
    {
        $modules = $this->modules;
        $modules['authLoginUserDetail'] = $this->authenticateLoginUserDetails ?? null;
        $modules['company_id'] = $modules['authLoginUserDetail']?->company_id;
        $modules['parent_type_id'] = $modules['authLoginUserDetail']?->parent_type_id;
        $loginUserId = $modules['authLoginUserDetail']?->id;

        try {
            $validator = Validator::make($request->all(), [
                'company_id' => ['required', Rule::exists((new Company())->getTable(), 'id')],
            ]);

            if ($validator->fails()) {
                return $this->sendError($validator->messages()->first(), $validator->messages(), [], 401);
            }

            $randomString = Helper::RandomString(32);
            $data = [
                'company_id' => $request->company_id,
                'api_code' => $randomString,
            ];

            return $this->sendResponse($data, 'Website API token generated successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->sendError('Validation failed.', $e->errors(), [], 422);
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong', $e->getMessage(), [], 500);
        }
    }

    public function get_master_social(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => [
                'required',
                Rule::exists((new Company())->getTable(), 'id')->where('status', 'active')
            ]
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->messages()->first(), $validator->messages(), [], 401);
        }
        try {
            $data = MasterSetting::where('company_id', $request?->company_id)->where('platform', $request?->platform);
            $data = $data->whereNotNull('platform');
            $data = $data->orderBy('id', 'asc');
            $data = $data->get();

            // return $data;
            return $this->sendResponse($data, "Master social list.");
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendError("Something want to wrong in register API");
    }

    /** company wise mail setting */
    public function mail_setting(Request $request, $id)
    {
        $isMasterAdmin = Auth::guard('admin_software')->check() || (Auth::guard('employees')->check() && Auth::guard('employees')->user()->company_id == 1);
        if (!$isMasterAdmin && Auth::guard('employees')->check()) {
            $loggedInCompanyId = Auth::guard('employees')->user()->company_id;
            if ((int)$id !== (int)$loggedInCompanyId) {
                abort(404);
            }
        }

        $modules = $this->modules;
        $modules['authLoginUserDetail'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails : null;
        $modules['company_id'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails?->company_id : null;
        $modules['parent_type_id'] = ($this->authenticateLoginUserDetails?->parent_type_id) ? $this->authenticateLoginUserDetails?->parent_type_id : null;
        $modules['form_route'] = route($modules['route'] . '.mail_setting', [$id]);
        $loginUserId = ($modules['authLoginUserDetail'] && $modules['authLoginUserDetail']?->id) ? $modules['authLoginUserDetail']?->id : null;
        // dd($modules);

        View::share('modules', $modules);

        try {
            if ($request?->_token) {

                // dd($request->all());
                $validator = Validator::make($request?->all(), [
                    'company_id' => ['required', 'integer', 'exists:' . (new Company())->getTable() . ',id'],
                    'mailer' => ['required', 'in:SMTP,sendmail,mail'], // Adjust if more options are allowed
                    'host' => ['required', 'string', 'max:255'],
                    'port' => ['required', 'integer'],
                    'username' => ['required'], // or 'string' if not strictly email
                    'password' => ['nullable', 'string', 'max:255'], // Allow empty value
                    'encryption' => ['nullable', 'in:ssl,tls'], // optional but restrict values
                    'from_address' => ['required', 'email'],
                    'from_name' => ['required', 'string', 'max:255'],
                    'bcc' => [
                        'nullable',
                        function ($attribute, $value, $fail) {
                            $emails = array_map('trim', explode(',', $value));
                            foreach ($emails as $email) {
                                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                                    $fail("The $attribute field contains an invalid email: $email");
                                }
                            }
                        }
                    ],
                ]);

                if ($validator->fails()) {
                    return Redirect::back()->withError($validator->messages())->withInput();
                }

                $company = Company::with(['MailSetting'])->findOrFail($id);

                $mailSetting = MailSetting::where('company_id', $request?->company_id)->first();

                $formArray = $request->except(['_token']);
                if (!$mailSetting) {
                    $formArray['created_by'] = $loginUserId;
                    // return $formArray;
                    MailSetting::create($formArray);
                    Session::flash('success', $company?->company_name . " Email setup successfully.");
                } else {
                    $formArray['updated_by'] = $loginUserId;
                    $mailSetting->update($formArray);
                    Session::flash('success', $company?->company_name . " Email setup successfully.");
                }
                // dd("L-809",$id, $mailSetting, $request->all());

                if (!empty($request?->team_set) && $request?->team_set === 'team_update' && !empty($request?->tab && $request?->tab === 'mail-tab')) {
                    return Redirect::route($modules['route'] . '.detail', ['id' => $id, 'tab' => 'mail-tab'])->withSuccess('Mail Setting update successfully');
                } else {
                    return Redirect::route($modules['route'] . '.mail_setting', [$id]);
                }
            }

            $company = Company::with(['MailSetting'])->findOrFail($id);

            View::share('company', $company);
            View::share('mail_setting', $company?->MailSetting);
            if ($request?->btn_submit === 'submit_and_exit') {
                return redirect()->route('company.index')
                    ->withSuccess('Company license details updated and redirected to company list.');
            }
            return view($modules['folder_path'] . '.mail_setting');
        } catch (\Exception $e) {
            return $e->getMessage();
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    /** company wise license setting */
    public function license_setting(Request $request, $id)
    {
        $isMasterAdmin = Auth::guard('admin_software')->check() || (Auth::guard('employees')->check() && Auth::guard('employees')->user()->company_id == 1);
        if (!$isMasterAdmin) {
            if ($request->ajax()) {
                return $this->sendError('Unauthorized', [], [], 403);
            }
            return redirect()->route('software.dashboard')->withErrors('Unauthorized');
        }

        $modules = $this->modules;
        $modules['authLoginUserDetail'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails : null;
        $modules['company_id'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails?->company_id : null;
        $modules['parent_type_id'] = ($this->authenticateLoginUserDetails?->parent_type_id) ? $this->authenticateLoginUserDetails?->parent_type_id : null;
        $modules['form_route'] = route($modules['route'] . '.license_setting', [$id]);
        $loginUserId = ($modules['authLoginUserDetail'] && $modules['authLoginUserDetail']?->id) ? $modules['authLoginUserDetail']?->id : null;
        // dd($modules);

        View::share('modules', $modules);

        try {
            if ($request?->_token) {

                // dd($request->all());
                $validator = Validator::make($request?->all(), [
                    'company_id' => ['required', 'integer', 'exists:' . (new Company())->getTable() . ',id'],
                    'plan_id' => ['required', 'exists:' . (new PlanMaster())->getTable() . ',id'],
                    'app_key' => ['nullable', 'string', 'min:8'],
                    'panel_url' => ['required', 'url'],
                    'max_employee_user_count' => ['required', 'integer', 'min:0'],
                    'app_right' => ['nullable', 'string'],
                    'panel_right' => ['required', 'string'],
                ], [
                    'plan_to.after_or_equal' => 'The plan to date must be after or equal to the plan from date.',
                    'panel_url.url' => 'The panel URL must be a valid URL.',
                ]);

                if ($validator->fails()) {
                    return Redirect::back()->withError($validator->messages())->withInput();
                }

                $updateData = Company::findOrFail($id);

                $formArray = $request->except(['_token']);


                $appRightArray = !empty($formArray['app_right']) ? array_filter(array_map('intval', explode(',', $formArray['app_right']))) : [];
                sort($appRightArray, SORT_NUMERIC);
                $formArray['app_right'] = implode(',', $appRightArray);

                $panelRightArray = !empty($formArray['panel_right']) ? array_filter(array_map('intval', explode(',', $formArray['panel_right']))) : [];
                sort($panelRightArray, SORT_NUMERIC);
                $formArray['panel_right'] = implode(',', $panelRightArray);



                if ($updateData) {
                    // $formArray['plan_from'] = Helper::convert_date($request?->plan_from, "d/m/Y", "Y/m/d");
                    // $formArray['plan_to'] = Helper::convert_date($request?->plan_to, "d/m/Y", "Y/m/d");
                    // $formArray['updated_by'] = $loginUserId;
                    if (!$request?->app_right) {
                        $formArray['app_right'] = [];
                    }

                    // return $formArray;
                    unset($formArray['plan_from']);
                    unset($formArray['plan_to']);
                    $updateData->update($formArray);

                    Session::flash('success', $updateData?->company_name . " Licence  detail successfully.");
                }

                if ($request?->btn_submit === 'submit_and_exit') {
                    return redirect()->route('company.index')
                        ->withSuccess('Company License Setting.');
                }
                // dd("L-870",$id, $updateData, $request->all());
                return Redirect::route($modules['route'] . '.license_setting', [$id]);
            }


            $company = Company::with(['plan'])->findOrFail($id);
            // Step 1: Get allowed panel_rights from the plan
            $allowedPanelRightIds = $company->plan && $company->plan->panel_right
                ? (is_array($company->plan->panel_right) ? $company->plan->panel_right : json_decode($company->plan->panel_right, true))
                : [];

            // Step 2: If it's a comma-separated string, convert to array
            if (!is_array($allowedPanelRightIds)) {
                $allowedPanelRightIds = explode(',', $allowedPanelRightIds);
            }

            // Step 3: Ensure all IDs are integers
            $allowedPanelRightIds = array_map('intval', $allowedPanelRightIds);

            // Step 4: Get all menus and filter by allowed ones
            $panel_right_list = Helper::getMainMenu(['platform' => 'panel']);

            foreach ($panel_right_list as $mainKey => &$main_menu) {
                $main_menu->sub_menu = collect($main_menu->sub_menu ?? [])->filter(function ($sub_menu) use ($allowedPanelRightIds) {
                    return in_array((int) $sub_menu->id, $allowedPanelRightIds);
                })->values();

                // Remove groups with no matching rights
                if ($main_menu->sub_menu->isEmpty()) {
                    unset($panel_right_list[$mainKey]);
                }
            }
            unset($main_menu);

            // Step 5: Share it to the view
            View::share('panel_right_list', $panel_right_list);


            $latestPlan = CompanySubscriptionPlan::where('company_id', $id)->where('plan_id', $company?->plan_id)->orderBy('id', 'desc')->first();

            if ($latestPlan && $latestPlan?->plan_id > 0 && !empty($latestPlan?->plan_from) && !empty($latestPlan?->plan_to)) {
                $company->setAttribute('plan_from', \Carbon\Carbon::parse($latestPlan?->plan_from)->format('d-m-Y'));
                $company->setAttribute('plan_to', \Carbon\Carbon::parse($latestPlan?->plan_to)->format('d-m-Y'));
            }
            View::share('company', $company);
            View::share('edit', $company?->plan);


            return view($modules['folder_path'] . '.license_setting');
        } catch (\Exception $e) {
            return $e->getMessage();
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    /** company upgrade plan */
    public function upgrade_plan(Request $request, $id)
    {
        $isMasterAdmin = Auth::guard('admin_software')->check() || (Auth::guard('employees')->check() && Auth::guard('employees')->user()->company_id == 1);
        if (!$isMasterAdmin) {
            if ($request->ajax()) {
                return $this->sendError('Unauthorized', [], [], 403);
            }
            return redirect()->route('software.dashboard')->withErrors('Unauthorized');
        }

        $modules = $this->modules;
        $modules['authLoginUserDetail'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails : null;
        $modules['company_id'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails?->company_id : null;
        $modules['parent_type_id'] = ($this->authenticateLoginUserDetails?->parent_type_id) ? $this->authenticateLoginUserDetails?->parent_type_id : null;
        $modules['form_route'] = route($modules['route'] . '.upgrade_plan', [$id]);
        $loginUserId = ($modules['authLoginUserDetail'] && $modules['authLoginUserDetail']?->id) ? $modules['authLoginUserDetail']?->id : null;


        View::share('modules', $modules);

        try {
            if ($request?->_token) {

                if (isset($request->is_plan_expire) && !empty($request->is_plan_expire)) {
                    $validator = Validator::make($request?->all(), [
                        'company_id' => ['required', 'integer', 'exists:' . (new Company())->getTable() . ',id'],
                        'plan_id' => ['required', 'exists:' . (new PlanMaster())->getTable() . ',id'],
                        'app_key' => ['nullable', 'string', 'min:8'],
                        'panel_url' => ['required', 'url'],
                    ], [
                        'panel_url.url' => 'The panel URL must be a valid URL.',
                    ]);

                    if ($validator->fails()) {
                        return Redirect::back()->withError($validator->messages())->withInput();
                    }

                    $updateData = Company::findOrFail($id);

                    $plan_data = PlanMaster::where('id', $request->plan_id)->first();
                    $validated['plan_id'] = $request->plan_id;
                    $validated['app_key'] = $request->app_key;
                    $plan_from = date('Y-m-d');
                    $plan_to = date('Y-m-d');
                    if (!empty($plan_data->plan_valid_day)) {
                        $plan_to = Carbon::now()->addDays((int) $plan_data->plan_valid_day)->format('Y-m-d');
                    }
                    $plan_to = $plan_to;
                    $validated['max_employee_user_count'] = $plan_data->max_employee_user_count;
                    $validated['app_right'] = $plan_data->app_right;
                    $validated['panel_right'] = $plan_data->panel_right;
                    $validated['panel_url'] = url('/software/login');

                    $updateData->update($validated);

                    $today = Carbon::today();
                    $subscription_status = '';
                    if ($plan_from && $plan_to) {
                        if ($today->between(Carbon::parse($plan_from), Carbon::parse($plan_to))) {
                            $subscription_status = 'active';
                        } else {
                            $subscription_status = 'expired';
                        }
                    }

                    CompanySubscriptionPlan::create([
                        'company_id' => $id,
                        'plan_id' => $request->plan_id,
                        'plan_from' => $plan_from,
                        'plan_to' => $plan_to,
                        'extra_detail' => json_encode($plan_data->toArray()),
                        'plan_expiry_date' => $plan_to,
                        'subscription_status' => $subscription_status,
                        'created_by' => $loginUserId,
                    ]);
                }

                if ($request?->btn_submit === 'submit_and_exit') {
                    return redirect()->route('company.index')
                        ->withSuccess('Company License Setting.');
                }
                // dd("L-870",$id, $updateData, $request->all());
                //return Redirect::route($modules['route'] . '.upgrade_plan', [$id]);
                return redirect()->route('company.index')->withSuccess('Company License Setting.');
            }

            $company = Company::with(['plan'])->findOrFail($id);

            $latestPlan = CompanySubscriptionPlan::where('company_id', $id)->where('plan_id', $company?->plan_id)->orderBy('id', 'desc')->first();
            $is_plan_expire = false;
            if ($latestPlan && $latestPlan?->subscription_status === 'expired') {
                $is_plan_expire = true;
                $plans = PlanMaster::where('status', 'active')->get();
                View::share('is_plan_expire', $is_plan_expire);
                View::share('plans', $plans);
            }

            View::share('company', $company);
            View::share('edit', $company?->plan);

            return view($modules['folder_path'] . '.upgrade_plan');
        } catch (\Exception $e) {
            return $e->getMessage();
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    public function detail(Request $request, $id)
    {
        $isMasterAdmin = Auth::guard('admin_software')->check() || (Auth::guard('employees')->check() && Auth::guard('employees')->user()->company_id == 1);
        if (!$isMasterAdmin && Auth::guard('employees')->check()) {
            $loggedInCompanyId = Auth::guard('employees')->user()->company_id;
            if ((int)$id !== (int)$loggedInCompanyId) {
                abort(404);
            }
        }

        $hasFullAccess = $this->checkCompanyDetailFullAccess();
        $isEmployee = !$hasFullAccess;

        View::share('isCompanyAdmin', $hasFullAccess);
        View::share('hasFullAccess', $hasFullAccess);
        View::share('isEmployee', $isEmployee);

        $modules = $this->modules;
        $modules['authLoginUserDetail'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails : null;
        $modules['company_id'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails?->company_id : null;
        $modules['parent_type_id'] = ($this->authenticateLoginUserDetails?->parent_type_id) ? $this->authenticateLoginUserDetails?->parent_type_id : null;
        $loginUserId = ($modules['authLoginUserDetail'] && $modules['authLoginUserDetail']?->id) ? $modules['authLoginUserDetail']?->id : null;
        // dd($modules);

        View::share('modules', $modules);

        try {   
            $company = Company::with(['plan'])->findOrFail($id);
            $tab = $request->query('tab');
            View::share('edit', $company);

            if (!$tab) {
                return redirect()->route('profile');
            }
            if ($isEmployee && $tab == 'subscription-tab') {
                return redirect()->route('company.detail', ['id' => $id, 'tab' => 'profile-tab']);
            }
            if ($tab == 'profile-tab') {
                $latestPlan = CompanySubscriptionPlan::where('company_id', $id)->where('plan_id', $company?->plan_id)->orderBy('id', 'desc')->first();
                if ($latestPlan && $latestPlan?->plan_id > 0 && !empty($latestPlan?->plan_from) && !empty($latestPlan?->plan_to)) {
                    $company->setAttribute('plan_from', \Carbon\Carbon::parse($latestPlan?->plan_from)->format('d-m-Y'));
                    $company->setAttribute('plan_to', \Carbon\Carbon::parse($latestPlan?->plan_to)->format('d-m-Y'));
                }
                View::share('company', $company);
            } else if ($tab == 'mail-tab') {
                View::share('mail_setting', $company?->MailSetting);
            } else if ($tab == 'subscription-tab') {

                // Calculate plans for Filter by Plan: Master Admin sees all, Company sees only their assigned plans
                $isMasterAdmin = Auth::guard('admin_software')->check() || (Auth::guard('employees')->check() && Auth::guard('employees')->user()->company_id == 1);

                if ($isMasterAdmin) {
                    $subscriptionPlanList = PlanMaster::where('status', 'active')->orderBy('id', 'desc')->get();
                } else {
                    $companyPlanIds = CompanySubscriptionPlan::where('company_id', $id)->pluck('plan_id')->toArray();
                    if ($company?->plan_id) {
                        $companyPlanIds[] = $company->plan_id;
                    }
                    $companyPlanIds = array_unique(array_filter($companyPlanIds));
                    $subscriptionPlanList = PlanMaster::whereIn('id', $companyPlanIds)->orderBy('id', 'desc')->get();
                }
                View::share('subscription_plan_list', $subscriptionPlanList);

                if ($request->ajax()) {
                    $data = CompanySubscriptionPlan::with(['plan', 'company'])->select('*')
                        ->where(function ($query) use ($modules, $loginUserId) {
                            // if (Auth::guard('employees')k()) {
                            //     $query->where('created_by', $loginUserId);
                            // }
                        })
                        ->where('company_id', $id)
                        ->orderBy('id', 'DESC');

                    $returnData = Datatables::of($data)
                        ->addIndexColumn()
                        ->filter(function ($query) use ($request) {
                            if ($request->has('filter_subscription_status_id') && $request->filter_subscription_status_id && $request->filter_subscription_status_id != 'all') {
                                $query->where('subscription_status', $request->filter_subscription_status_id);
                            }
                            if ($request->has('filter_plan') && $request->filter_plan) {
                                $query->where('plan_id', $request->filter_plan);
                            }
                            if ($request->has('search')) {
                                $search = $request->search;

                                $query->where(function ($q) use ($search) {
                                    $q->WhereHas('plan', function ($q2) use ($search) {
                                        $q2->where('name', 'like', "%{$search}%");
                                    });
                                });
                            }

                            if ($request->filled('filter_plan_date')) {
                                [$fromRaw, $toRaw] = explode(' to ', $request->filter_plan_date);

                                $fromDate = Helper::convert_date(trim($fromRaw), "d/m/Y", "Y-m-d");
                                $toDate = Helper::convert_date(trim($toRaw), "d/m/Y", "Y-m-d");

                                if ($fromDate && $toDate) {
                                    $table = (new CompanySubscriptionPlan())->getTable();
                                    $query->whereBetween(DB::raw("DATE($table.plan_expiry_date)"), [$fromDate, $toDate]);
                                }
                            }
                        })
                        ->editColumn('company_arrow', function ($row) {

                            $sub_addon_count = CompanySubscriptionAddons::where('company_id', $row->company_id)->where('plan_id', $row->plan_id)->where('subscription_plan_id', $row->id)->count();

                            $arrow_row = '';
                            if ($sub_addon_count > 0) {
                                $arrow_row .= '<span class="toggle-icon company_sub_addon_expand cursor-pointer"
                                            data-company_id="' . $row->company_id . '"
                                            data-plan_id="' . $row->plan_id . '"
                                            data-subscription_id="' . $row->id . '">
                                            <i class="fa fa-caret-right fa-lg"></i>&nbsp;&nbsp;' . $row?->plan?->name . '
                                        </span>';
                            } else {
                                $arrow_row .= '<span>&nbsp;&nbsp;' . $row?->plan?->name . '</span>';
                            }
                            return $arrow_row;
                        })
                        ->editColumn('plan_from', function ($row) {
                            return \Carbon\Carbon::parse($row?->plan_from)->format('d-m-Y');
                        })
                        ->editColumn('plan_to', function ($row) {
                            return \Carbon\Carbon::parse($row?->plan_to)->format('d-m-Y');
                        })
                        ->editColumn('plan_expiry_date', function ($row) {
                            return \Carbon\Carbon::parse($row->plan_expiry_date)->format('d-m-Y');
                        })
                        ->editColumn('subscription_status', function ($row) use ($modules) {
                            if ($row->subscription_status == 'active') {
                                return $row->subscription_status;
                            } else {
                                return '<span class="badge bg-glow" style="background-color: #d66363">' . $row->subscription_status . '</span>';
                            }
                        })
                        ->addColumn('action', function ($row) use ($modules) {
                            $btn = '';
                            if (!$row?->deleted_at) {
                                if ($row->subscription_status == 'active') {
                                    $btn .= '<a href="javascript:void(0);" id="add_days_subscription_btn" data-company_id="' . $row->company_id . '" data-plan_id="' . $row->plan_id . '" data-subscription_id="' . $row->id . '" class="btn btn-primary btn-sm waves-effect waves-light text-white open-add-days-assign-modal"><i class="menu-icon ti ti-calendar"></i> Add days</a>';
                                }
                            }
                            if ($btn == '') {
                                $btn = '-';
                            }
                            return $btn;
                        })
                        ->rawColumns(['subscription_status', 'action', 'company_arrow'])
                        ->make(true);

                    $jsonData = $returnData->getData(true);
                    return response()->json($jsonData);
                }
            }

            return view($modules['folder_path'] . '.update_company_detail');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface | \Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            throw $e;
        } catch (\Exception $e) {
            return $e->getMessage();
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * Check if the authenticated user has full access to company details.
     * Full access users:
     * - Master Admin (admin_software guard or employee with company_id == 1)
     * - Main Admin (company root employee: parent_id == 0 or null)
     * - Main HR (role name contains 'hr' or designation contains 'hr')
     * - Department Head (role or designation contains 'department head' or 'dept head' or 'hod')
     * - Supervisor (role or designation contains 'supervisor')
     */
    private function checkCompanyDetailFullAccess(): bool
    {
        $isMasterAdmin = Auth::guard('admin_software')->check() || (Auth::guard('employees')->check() && Auth::guard('employees')->user()->company_id == 1);
        if ($isMasterAdmin) {
            return true;
        }

        if (Auth::guard('employees')->check()) {
            $user = Auth::guard('employees')->user();
            if ((int)$user->parent_id === 0 || $user->parent_id === null || $user->parent_id === '') {
                return true;
            }

            $roleName = strtolower(trim($user->teamRole?->name ?? ''));
            $desigName = strtolower(trim($user->designation?->name ?? ''));

            $isMainHr = str_contains($roleName, 'hr') || str_contains($desigName, 'hr');
            $isDeptHead = str_contains($roleName, 'department head') || str_contains($roleName, 'dept head') || str_contains($roleName, 'hod') || str_contains($desigName, 'department head') || str_contains($desigName, 'hod');
            $isSupervisor = str_contains($roleName, 'supervisor') || str_contains($desigName, 'supervisor');

            return $isMainHr || $isDeptHead || $isSupervisor;
        }

        return false;
    }
}
