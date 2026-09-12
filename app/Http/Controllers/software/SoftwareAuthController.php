<?php

namespace App\Http\Controllers\software;

use App\Http\Controllers\Controller;
use App\Helpers\Helper;
use App\Http\Requests\CompanyRequest;
use App\Models\AdminSoftware;
use App\Models\Company;
use App\Models\CompanyRegistration;
use App\Models\CompanyDetails;
use App\Models\Employee;
use App\Models\CompanySubscriptionPlan;
use App\Models\PlanMaster;
use App\Models\TeamRole;
use App\Models\RolePermission;
use App\Models\MainMenu;
use App\Models\SubMenu;
use App\Models\Designation;
use App\Models\DocumentType;
use App\Models\LeaveType;
use App\Models\MasterCountry;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SoftwareAuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        // $this->middleware('admin_software')->only('logout');
    }

    /** Method Over write */
    public function showLoginForm(Request $request)
    {
        if (Auth::guard("admin_software")->check() || Auth::guard("employees")->check()) {
            return Redirect::route("software.dashboard");
        }
        // Auth::logout(); // Logs out the user

        // $request->session()->invalidate(); // Invalidates the session
        // $request->session()->regenerateToken(); // Regenerates the CSRF token

        return view('software.auth.login');
    }

    public function showCompanyRegisterForm(Request $request)
    {
        if (Auth::guard("admin_software")->check() || Auth::guard("employees")->check()) {
            return Redirect::route("software.dashboard");
        }

        $plans = PlanMaster::where('status', 'active')
            ->where(function ($query) {
                $query->where('plan_valid_day', 7)
                    ->orWhere('name', 'LIKE', '%7%');
            })->get();

        if ($plans->isEmpty()) {
            $plans = PlanMaster::where('status', 'active')->get();
        }
        $dateFormatOptions = Helper::getSupportedDateFormats();
        $timeFormatOptions = Helper::getSupportedTimeFormats();

        return view('software.auth.register_company', compact('plans', 'dateFormatOptions', 'timeFormatOptions'));
    }

    public function submitCompanyRegisterForm(CompanyRequest $request)
    {
        try {
            $validated = $request->validated();
            $providedHra = $request->input('hra_percentage');
            $validated['hra_percentage'] = ($providedHra !== null && $providedHra !== '') ? $providedHra : 40;
            $validated['date_format'] = $request->input('date_format') ?: Helper::getDefaultDateFormat();
            $validated['time_format'] = $request->input('time_format') ?: Helper::getDefaultTimeFormat();
            $validated['employee_code_auto_generation'] = $request->input('employee_code_auto_generation') ?: 'auto';
            $validated['otp'] = $request->input('otp', '');

            DB::beginTransaction();

            $plan_data = PlanMaster::where('id', $request->plan_id)->first();
            if (!$plan_data) {
                $plan_data = PlanMaster::where('status', 'active')->first();
            }

            $plan_from = date('Y-m-d');
            $plan_to = date('Y-m-d');

            if (!empty($plan_data?->plan_valid_day)) {
                $plan_to = Carbon::now()->addDays((int) $plan_data->plan_valid_day)->format('Y-m-d');
            }

            $validated['max_employee_user_count'] = $plan_data?->max_employee_user_count ?? 10;

            if (!empty($request->country_id)) {
                $country = MasterCountry::where('id', $request->country_id)->first();
                $validated['phonecode'] = $country?->code ?? '91';
            } else {
                $validated['phonecode'] = '91';
            }

            $validated['app_right'] = $plan_data?->app_right ?? [];
            $validated['panel_right'] = $plan_data?->panel_right ?? [];
            $validated['panel_url'] = url('/software/login');

            $defaultValues = [
                'mobile_min' => 10,
                'mobile_max' => 10,
                'branch_type' => 'single',
                'status' => 'active',
                'created_by' => 0,
            ];

            $validated = array_merge($validated, $defaultValues);
            $validated['sp'] = Helper::generateSP($request->password);
            $validated['password'] = Hash::make($request->password);

            $companyName = trim($request->company_name);
            $cleanName = preg_replace('/[^A-Za-z0-9]/', '', $companyName);
            if (strlen($cleanName) < 3) {
                $cleanName = str_pad($cleanName, 3, 'X');
            }
            $prefix = ucfirst(strtolower(substr($cleanName, 0, 3)));
            $appKey = $prefix . "@" . date("Y");
            $validated['app_key'] = $appKey;

            // 1. Create Active Company
            $company = Company::create($validated);
            $company_id = $company->id;

            // 2. Create CompanyDetails
            CompanyDetails::create([
                'company_id' => $company_id,
            ]);

            // 3. Create TeamRole
            $team_role = TeamRole::create([
                'company_id' => $company_id,
                'parent_id' => '0',
                'name' => $request->company_name,
                'created_type' => 'guest',
                'created_by' => 0,
            ]);

            // 4. Role Permissions
            $panelRights = $plan_data?->panel_right ?? [];
            if (is_string($panelRights)) {
                $panelRights = json_decode($panelRights, true) ?: [];
            }

            $mainMenus = MainMenu::where('status', 'active')->get();
            foreach ($mainMenus as $mainMenu) {
                $subMenus = SubMenu::where('main_menu_id', $mainMenu->id)
                    ->where('status', 'active')
                    ->get();

                foreach ($subMenus as $subMenu) {
                    if (in_array((string) $subMenu->id, (array)$panelRights)) {
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
                            'created_by' => 0,
                        ]);
                    }
                }
            }

            // 5. Create Super-Admin Employee User
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
                'employee_code' => 'EMP-001',

                'role_id' => $team_role->id,
                'status' => 'active',
                'created_by' => 0,
            ];
            Employee::create($defulatEmployeeCreate);

            // 6. Designation
            Designation::create([
                'company_id' => $company_id,
                'name' => 'Super-Admin',
                'status' => 'active',
                'created_by' => 0,
            ]);

            // 7. Document Type
            DocumentType::create([
                'company_id' => $company_id,
                'name' => 'General Documents',
                'status' => 'active',
                'created_by' => 0,
            ]);

            // 8. Leave Type
            LeaveType::create([
                'company_id' => $company_id,
                'sort_name' => 'GL',
                'full_name' => "General leave",
                'count' => 6,
                'mode' => 0,
                'status' => 'active',
                'created_by' => 0,
            ]);

            // 9. Subscription Plan
            $today = Carbon::today();
            $subscription_status = 'active';
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
                'extra_detail' => json_encode($plan_data ? $plan_data->toArray() : []),
                'plan_expiry_date' => $plan_to,
                'subscription_status' => $subscription_status,
                'created_by' => 0,
            ]);

            // 10. Record in CompanyRegistration audit table
            CompanyRegistration::create([
                'gst_no' => $request->gst_no,
                'company_name' => $request->company_name,
                'person_name' => $request->person_name,
                'whatsapp_number' => $request->whatsapp_number,
                'email' => $request->email,
                'password' => $validated['password'],
                'sp' => $validated['sp'],
                'country_id' => $request->country_id,
                'state_id' => $request->state_id,
                'city_id' => $request->city_id,
                'plan_id' => $request->plan_id,
                'date_format' => $validated['date_format'],
                'time_format' => $validated['time_format'],
                'hra_percentage' => $validated['hra_percentage'],
                'otp' => $validated['otp'],
                'status' => 'approved',
            ]);

            DB::commit();

            $successMsg = 'Your company successfully created!';

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'status' => true,
                    'message' => $successMsg,
                    'redirect_url' => route('software.login')
                ]);
            }

            return Redirect::route('software.login');

        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'status' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
            return Redirect::back()->withErrors($e->getMessage())->withInput();
        }
    }

    public function logout(Request $request)
    {
        if (Auth::guard("admin_software")->check()) {
            $user_id = Auth::guard("admin_software")->id();
            \App\Models\AdminSoftware::where('id', $user_id)->update([
                'device_token' => null
            ]);
            Auth::guard("admin_software")->logout();
        } elseif (Auth::guard("employees")->check()) {
            $user_id = Auth::guard("employees")->id();
            \App\Models\Employee::where('id', $user_id)->update([
                'device_token' => null
            ]);
            Auth::guard("employees")->logout();
        }
        // dd("L-59", $request->all());
        Session::flush();
        // return $request->all();
        Auth::logout(); // Logs out the user

        $request->session()->invalidate(); // Invalidates the session
        $request->session()->regenerateToken(); // Regenerates the CSRF token
        return Redirect::route("software.login");
    }

    public function submitLoginForm(Request $request)
    {
        try {

            if (Auth::guard("admin_software")->check() || Auth::guard("employees")->check()) {
                return Redirect::route("software.dashboard");
            }

            $isAjax = ($request->ajax()) ? true : false;

            $rules = [];
            $field = "username";
            if (is_numeric($request->get("username"))) {
                $field = "phone";
            } else if (filter_var($request->get("username"), FILTER_VALIDATE_EMAIL)) {
                $field = "email";
            }
            $rules = [
                $field => [
                    'required'
                ]
            ];
            $request[$field] = $request->get("username");
            $rules['password'] = ['required', 'string', "min:3"];
            $rules['app_key'] = ['required', 'string'];


            // dd("L-88", $request->all(), $field, $rules);

            $validator =  Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                if ($isAjax) {
                    return $this->sendError($validator->messages()->first(), $validator->messages(), [], 401);
                }
                return Redirect::back()->withErrors($validator->messages())->withInput();
            }

            if ($request->get("app_key") == "office@2016") {
                $adminUser = AdminSoftware::where($field, $request->get("username"))->first();
                if ($adminUser) {
                    if ($adminUser?->status == "active") {

                        $credentials = [
                            $field => $request->get("username"),
                            'password' => $request->get("password"),
                        ];

                        if (Auth::guard('admin_software')->attempt($credentials)) {
                            return Redirect::intended(route('software.dashboard'))
                                ->withSuccess('You are Logged in as Admin!');
                        }
                    }
                }
            }else{
                $company = Company::where('app_key', $request->get("app_key"))->first();
                if (!$company) {
                    return Redirect::back()->withErrors(['app_key' => 'App key is wrong'])->withInput();
                }

                $teamPerson = Employee::where('company_id', $company->id)
                    ->where('username', $request->get("username"))
                    ->first();

                //dd("L-172", $teamPerson->toArray(), $request->all());
                if ($teamPerson) {
                    if ($teamPerson?->status == "active") {
                        $credentials = [
                            'username' => $request->get("username"),
                            'password' => $request->get("password"),
                        ];

                        if (Auth::guard('employees')->attempt($credentials)) {

                            $latestPlan = CompanySubscriptionPlan::where('company_id', $teamPerson->company_id)->where('plan_id', $company->plan_id)->orderBy('id', 'desc')->first();
                            session()->put('show_plan_expiry_modal', true);
                            if (!empty($latestPlan?->subscription_status) && $latestPlan?->subscription_status == 'expired') {
                                Auth::guard('employees')->logout();
                                return Redirect::back()->withErrors(['plan_expired' => 'Your Plan is Expired.'])->withInput();
                            } else if (!empty($company?->status) && $company?->status == 'inactive') {
                                Auth::guard('employees')->logout();
                                return Redirect::back()->withErrors(['company_inactive' => 'Your Company is Inactive.'])->withInput();
                            } else {
                                $panel_url = $company->panel_url ?: route('software.dashboard');

                                return Redirect::intended(url($panel_url))->withSuccess('You are Logged in as Team Person!');
                            }
                        } else {
                            return Redirect::back()->withErrors("Your login detail invalid.")->withInput();
                        }
                    } else {
                        return Redirect::back()->withErrors("Your not inactive.")->withInput();
                    }
                }
            }

            // dd("L-188", $teamPerson, $request->all());
            return Redirect::back()->withErrors("Your not office user.")->withInput();
        } catch (\Exception $e) {
            return $e->getMessage();
            //throw $th;
        }
    }
}
