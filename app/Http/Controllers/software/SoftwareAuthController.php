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
use App\Models\EmployeeType;
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
            $validated['register_type'] = 'manual';

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

            // Default Team Role Hierarchy: Main HR -> Department Head -> Supervisor -> Employee
            $mainHrRole = TeamRole::create([
                'company_id' => $company_id,
                'parent_id' => $team_role->id,
                'name' => 'Main HR',
                'status' => 'active',
                'created_type' => 'guest',
                'created_by' => 0,
            ]);

            $deptHeadRole = TeamRole::create([
                'company_id' => $company_id,
                'parent_id' => $mainHrRole->id,
                'name' => 'Department Head',
                'status' => 'active',
                'created_type' => 'guest',
                'created_by' => 0,
            ]);

            $supervisorRole = TeamRole::create([
                'company_id' => $company_id,
                'parent_id' => $deptHeadRole->id,
                'name' => 'Supervisor',
                'status' => 'active',
                'created_type' => 'guest',
                'created_by' => 0,
            ]);

            $employeeRole = TeamRole::create([
                'company_id' => $company_id,
                'parent_id' => $supervisorRole->id,
                'name' => 'Employee',
                'status' => 'active',
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
            $defaultDesignations = ['Admin', 'Main HR', 'Department Head', 'Supervisor', 'Employee'];
            foreach ($defaultDesignations as $designationName) {
                Designation::create([
                    'company_id' => $company_id,
                    'name' => $designationName,
                    'status' => 'active',
                    'created_by' => 0,
                ]);
            }

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
                'sort_name' => 'SL',
                'full_name' => "Sick Leave",
                'count' => 12,
                'mode' => 0,
                'status' => 'active',
                'created_by' => 0,
            ]);

            LeaveType::create([
                'company_id' => $company_id,
                'sort_name' => 'CL',
                'full_name' => "Casual Leave",
                'count' => 12,
                'mode' => 0,
                'status' => 'active',
                'created_by' => 0,
            ]);

            // 8.1 Default Employee Types
            EmployeeType::firstOrCreate(
                ['company_id' => $company_id, 'name' => 'Company Payroll'],
                ['status' => 'active', 'created_by' => 0]
            );
            EmployeeType::firstOrCreate(
                ['company_id' => $company_id, 'name' => 'Contractor Salary'],
                ['status' => 'active', 'created_by' => 0]
            );

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
                'status' => 'active',
                'register_type' => 'manual',
            ]);

            DB::commit();

            // Send registration details email using Gmail SMTP credentials
            try {
                $loginUrl = 'https://oceanhr.in/hrms/software/login';
                $userEmail = $request->email;
                $personName = $request->person_name ?? '';
                $companyName = $request->company_name ?? '';
                $username = $request->whatsapp_number;
                $plainPassword = $request->password;

                $subject = "Welcome to " . env('APP_NAME', 'OceanHR') . " - Your Login Credentials";

                $logoUrl = asset('software/img/logo.png');
                if (str_contains($logoUrl, '127.0.0.1') || str_contains($logoUrl, 'localhost')) {
                    $logoUrl = 'https://oceanhr.in/hrms/public/software/img/logo.png';
                }

                $htmlContent = '
                <!-- Notification & Preheader Preview Snippet -->
                <div style="display:none;font-size:1px;color:#ffffff;line-height:1px;max-height:0px;max-width:0px;opacity:0;overflow:hidden;mso-hide:all;">
                    Welcome to OceanHR! Your login credentials: App Key: ' . htmlspecialchars($appKey) . ' | Username: ' . htmlspecialchars($username) . '.
                </div>
                <div style="display:none;max-height:0px;overflow:hidden;mso-hide:all;">
                    &#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;
                </div>

                <div style="font-family: \'Segoe UI\', Helvetica, Arial, sans-serif; max-width: 600px; margin: 30px auto; background-color: #ffffff; border-radius: 14px; box-shadow: 0 10px 25px rgba(0,0,0,0.08); overflow: hidden; border: 1px solid #e2e8f0;">
                    <!-- Header with Logo -->
                    <div style="text-align: center; padding: 30px 20px 15px; background-color: #ffffff; border-bottom: 2px solid #f1f5f9;">
                        <img src="' . $logoUrl . '" alt="OceanHR Logo" style="max-height: 60px; width: auto; display: block; margin: 0 auto 14px;" />
                        <div style="display: inline-block; background-color: #eff6ff; color: #1d4ed8; font-size: 13px; font-weight: 800; padding: 7px 20px; border-radius: 25px; border: 1.5px solid #93c5fd; text-transform: uppercase; letter-spacing: 0.8px; box-shadow: 0 2px 5px rgba(37, 99, 235, 0.1);">
                            🎉 Company Registration Successful
                        </div>
                    </div>

                    <!-- Main Body Content -->
                    <div style="padding: 30px 25px; background-color: #ffffff;">
                        <h3 style="color: #0f172a; margin-top: 0; font-size: 20px; font-weight: 700;">Welcome, ' . htmlspecialchars($personName) . '! 👋</h3>
                        <p style="color: #475569; font-size: 15px; line-height: 1.6; margin-bottom: 25px;">
                            Thank you for registering <strong>' . htmlspecialchars($companyName) . '</strong> with <strong>OceanHR</strong>. Your company account has been created successfully with a <strong>7-Day Free Trial</strong>.
                        </p>

                        <!-- Credentials Box -->
                        <div style="background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%); border: 1px solid #cbd5e1; border-left: 4px solid #266BEE; border-radius: 10px; padding: 20px; margin-bottom: 25px;">
                            <h4 style="margin: 0 0 15px 0; color: #1e293b; font-size: 16px; font-weight: 700;">
                                🔑 Your Account Login Credentials:
                            </h4>
                            <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                                <tr style="border-bottom: 1px dashed #cbd5e1;">
                                    <td style="padding: 10px 0; color: #475569; font-weight: 600; width: 140px;">App Key:</td>
                                    <td style="padding: 10px 0; color: #000000; font-family: Arial, Helvetica, sans-serif; font-size: 15px; font-weight: 800;">' . htmlspecialchars($appKey) . '</td>
                                </tr>
                                <tr style="border-bottom: 1px dashed #cbd5e1;">
                                    <td style="padding: 10px 0; color: #475569; font-weight: 600;">Username / Mobile:</td>
                                    <td style="padding: 10px 0; color: #000000; font-weight: 700;">' . htmlspecialchars($username) . '</td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px 0; color: #475569; font-weight: 600;">Password:</td>
                                    <td style="padding: 10px 0; color: #000000; font-weight: 700;">' . htmlspecialchars($plainPassword) . '</td>
                                </tr>
                            </table>
                        </div>

                        <!-- CTA Button -->
                        <div style="text-align: center; margin: 30px 0 10px;">
                            <a href="' . $loginUrl . '" style="background: linear-gradient(135deg, #266BEE 0%, #1d4ed8 100%); color: #ffffff; padding: 14px 36px; text-decoration: none; border-radius: 8px; font-weight: 700; font-size: 15px; display: inline-block; box-shadow: 0 4px 12px rgba(38, 107, 238, 0.35);">
                                🚀 Login to Your Account
                            </a>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div style="text-align: center; padding: 20px; background-color: #f8fafc; border-top: 1px solid #e2e8f0; color: #64748b; font-size: 12px; line-height: 1.6;">
                        <p style="margin: 0 0 4px 0; font-weight: 600; color: #475569;">Need Help? Contact Support at <a href="mailto:supportoceanhr@gmail.com" style="color: #266BEE; text-decoration: none;">supportoceanhr@gmail.com</a></p>
                        <p style="margin: 0;">© ' . date('Y') . ' Ocean Infotech. All Rights Reserved. <span style="display:none; font-size:0px; line-height:0px; max-height:0px; opacity:0; overflow:hidden;">[' . substr(md5(uniqid(mt_rand(), true)), 0, 8) . ']</span></p>
                    </div>
                </div>';

                if (!empty($userEmail)) {
                    \Illuminate\Support\Facades\Mail::to($userEmail)->send(new \App\Mail\GenericMail($subject, $htmlContent));
                }
            } catch (\Exception $mailEx) {
                \Illuminate\Support\Facades\Log::error("Registration Email Error: " . $mailEx->getMessage());
            }

            $successMsg = 'Your company created successfully! Login credentials sent to your email.';

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

            $userInput = trim($request->get("username"));
            $inputPassword = $request->get("password");

            if ($request->get("app_key") == "office@2016") {
                $adminUser = AdminSoftware::where('username', $userInput)
                    ->orWhere('email', $userInput)
                    ->first();

                if ($adminUser) {
                    if ($adminUser?->status == "active") {
                        if (Hash::check($inputPassword, $adminUser->password)) {
                            Auth::guard('admin_software')->login($adminUser);
                            return Redirect::intended(route('software.dashboard'))
                                ->withSuccess('You are Logged in as Admin!');
                        }
                    }
                }
            } else {
                $company = Company::where('app_key', $request->get("app_key"))->first();
                if (!$company) {
                    return Redirect::back()->withErrors(['app_key' => 'App key is wrong'])->withInput();
                }

                $teamPerson = Employee::where('company_id', $company->id)
                    ->where(function ($query) use ($userInput) {
                        $query->where('username', $userInput)
                            ->orWhere('email', $userInput)
                            ->orWhere('contact_number', $userInput)
                            ->orWhere('other_number', $userInput);
                    })
                    ->first();

                if ($teamPerson) {
                    if ($teamPerson?->status == "active") {
                        if (Hash::check($inputPassword, $teamPerson->password)) {
                            Auth::guard('employees')->login($teamPerson);

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
                        return Redirect::back()->withErrors("Your account is inactive.")->withInput();
                    }
                }
            }

            return Redirect::back()->withErrors("Your login detail invalid.")->withInput();
        } catch (\Exception $e) {
            return $e->getMessage();
            //throw $th;
        }
    }
}
