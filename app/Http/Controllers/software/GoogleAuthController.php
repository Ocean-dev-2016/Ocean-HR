<?php

namespace App\Http\Controllers\software;

use App\Http\Controllers\Controller;
use App\Helpers\Helper;
use App\Models\Company;
use App\Models\CompanyDetails;
use App\Models\CompanyRegistration;
use App\Models\CompanySubscriptionPlan;
use App\Models\Designation;
use App\Models\DocumentType;
use App\Models\Employee;
use App\Models\EmployeeType;
use App\Models\LeaveType;
use App\Models\MainMenu;
use App\Models\PlanMaster;
use App\Models\RolePermission;
use App\Models\SubMenu;
use App\Models\TeamRole;
use App\Mail\GenericMail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    /**
     * Redirect to Google OAuth
     */
    public function redirectToGoogle(Request $request)
    {
        try {
            return Socialite::driver('google')->redirect();
        } catch (\Exception $e) {
            Log::error("Google OAuth Redirect Error: " . $e->getMessage());
            return redirect()->route('software.login')->with('error', 'Unable to connect with Google. Please try again.');
        }
    }

    /**
     * Redirect to Google for Sign-In (Alias for single Google route)
     */
    public function redirectToGoogleSignIn()
    {
        return $this->redirectToGoogle(request());
    }

    /**
     * Redirect to Google for Sign-Up (Alias for single Google route)
     */
    public function redirectToGoogleSignUp()
    {
        return $this->redirectToGoogle(request());
    }

    /**
     * Handle the callback from Google authentication.
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            $email = $googleUser->getEmail();
            $name = $googleUser->getName() ?: 'User';
            $googleId = $googleUser->getId();

            if (empty($email)) {
                return redirect()->route('software.login')->with('error', 'Google account email not found.');
            }

            // 1. Check if Employee already exists by email AND its Company is valid/exists
            $existingEmployee = Employee::where('email', $email)
                ->whereNull('deleted_at')
                ->whereHas('company', function ($q) {
                    $q->whereNull('deleted_at');
                })
                ->first();

            if ($existingEmployee) {
                if ($googleId && empty($existingEmployee->google_id)) {
                    $existingEmployee->update(['google_id' => $googleId]);
                }
                Auth::guard('employees')->login($existingEmployee);
                return redirect()->route('software.dashboard')->with('success', 'Welcome back! Logged in successfully with Google.');
            }

            // 2. Check if Company exists by email
            $existingCompany = Company::where('email', $email)->whereNull('deleted_at')->first();

            if ($existingCompany) {
                $employee = Employee::where('company_id', $existingCompany->id)->whereNull('deleted_at')->first();
                if ($employee) {
                    if ($googleId && empty($employee->google_id)) {
                        $employee->update(['google_id' => $googleId]);
                    }
                    Auth::guard('employees')->login($employee);
                } else {
                    Auth::guard('admin_software')->login($existingCompany);
                }
                return redirect()->route('software.dashboard')->with('success', 'Welcome back! Logged in successfully with Google.');
            }

            // 3. New User Registration: Generate 6-Digit Email OTP and start verification
            $otp = (string) rand(100000, 999999);

            // Send Email OTP first so sending latency does not consume the 2-minute window
            $this->sendOtpEmail($email, $name, $otp);

            // Store pending registration data in session (2 minutes validity)
            session([
                'google_pending_signup' => [
                    'email' => $email,
                    'name' => $name,
                    'google_id' => $googleId,
                    'otp' => $otp,
                    'otp_expires_at' => Carbon::now()->addMinutes(2)->timestamp,
                ]
            ]);

            return redirect()->route('software.auth.google.verify-otp-form')->with('success', 'Welcome! We have sent a 6-digit OTP to your Google email (' . $email . ') to complete your registration.');
        } catch (\Exception $e) {
            Log::error("Google OAuth Callback Error: " . $e->getMessage());
            return redirect()->route('software.login')->with('error', 'Google authentication failed: ' . $e->getMessage());
        }
    }

    /**
     * Show OTP Verification Form
     */
    public function showOtpForm()
    {
        $pending = session('google_pending_signup');
        if (!$pending || empty($pending['email'])) {
            return redirect()->route('software.login')->with('error', 'Session expired. Please try Google sign-up again.');
        }

        // If already verified, direct to company setup
        if (!empty($pending['otp_verified'])) {
            return redirect()->route('software.auth.google.company-setup-form');
        }

        $pendingEmail = $pending['email'];
        $expiresAt = $pending['otp_expires_at'] ?? (time() + 120);
        $remainingSeconds = max(0, $expiresAt - time());

        return view('software.auth.verify_google_otp', compact('pendingEmail', 'remainingSeconds'));
    }

    /**
     * Verify OTP and move to Company Setup Step
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|numeric|digits:6',
        ], [
            'otp.required' => 'Please enter the 6-digit OTP code.',
            'otp.digits' => 'OTP must be exactly 6 digits.',
        ]);

        $pending = session('google_pending_signup');

        if (!$pending || empty($pending['email'])) {
            return redirect()->route('software.login')->with('error', 'Session expired. Please try Google sign-up again.');
        }

        // Security Check 1: OTP Expiration
        if (time() > ($pending['otp_expires_at'] ?? 0)) {
            return redirect()->back()->withInput()->with('error', 'OTP has expired! Please click "Resend OTP" to get a new code (Valid for 2 minutes).');
        }

        // Security Check 2: Brute-Force Attack Prevention (Max 5 attempts allowed)
        $attempts = ($pending['otp_attempts'] ?? 0) + 1;
        $pending['otp_attempts'] = $attempts;

        if ($attempts > 5) {
            session()->forget('google_pending_signup');
            return redirect()->route('software.login')->with('error', 'Too many invalid OTP attempts! For security reasons, please start Google sign-up again.');
        }

        if (trim($request->otp) !== trim($pending['otp'])) {
            session(['google_pending_signup' => $pending]);
            $remaining = 5 - $attempts;
            return redirect()->back()->withInput()->with('error', 'Invalid OTP code. (' . $remaining . ' attempts remaining)');
        }

        // OTP is verified! Mark verified in session and reset attempts
        $pending['otp_verified'] = true;
        $pending['otp_attempts'] = 0;
        session(['google_pending_signup' => $pending]);

        return redirect()->route('software.auth.google.company-setup-form')->with('success', 'OTP verified successfully! Now, please enter your company details.');
    }

    /**
     * Show Company Setup Form (Step 2 after OTP verification)
     */
    public function showCompanySetupForm()
    {
        $pending = session('google_pending_signup');

        if (!$pending || empty($pending['email'])) {
            return redirect()->route('software.login')->with('error', 'Session expired. Please try Google sign-up again.');
        }

        // Security Check 3: Step Enforcement (Cannot access without verifying OTP)
        if (empty($pending['otp_verified'])) {
            return redirect()->route('software.auth.google.verify-otp-form')->with('error', 'Please verify your OTP first.');
        }

        $pendingEmail = $pending['email'];
        $pendingName = $pending['name'] ?? '';
        $suggestedCompanyName = $pending['company_name'] ?? '';

        return view('software.auth.google_company_setup', compact('pendingEmail', 'pendingName', 'suggestedCompanyName'));
    }

    /**
     * Finalize Company Setup, Register Account & Login
     */
    public function submitCompanySetup(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|min:2|max:190|unique:companies,company_name',
            'whatsapp_number' => 'required|numeric|digits:10|unique:companies,whatsapp_number',
        ], [
            'company_name.required' => 'Please enter your Company Name.',
            'company_name.unique' => 'This company name is already registered.',
            'company_name.min' => 'Company Name must be at least 2 characters.',
            'whatsapp_number.required' => 'Please enter your 10-digit WhatsApp/Mobile Number.',
            'whatsapp_number.unique' => 'This WhatsApp/mobile number is already registered.',
            'whatsapp_number.digits' => 'WhatsApp/Mobile number must be exactly 10 digits.',
        ]);

        $pending = session('google_pending_signup');

        if (!$pending || empty($pending['email'])) {
            return redirect()->route('software.login')->with('error', 'Session expired. Please try Google sign-up again.');
        }

        // Security Check 4: Step Enforcement
        if (empty($pending['otp_verified'])) {
            return redirect()->route('software.auth.google.verify-otp-form')->with('error', 'Please verify your OTP first.');
        }

        // Security Check: Duplicate Company/Email Protection
        $email = $pending['email'];
        $existingCompany = Company::where('email', $email)->first();
        $existingEmployee = Employee::where('email', $email)->whereNull('deleted_at')->first();

        if ($existingCompany || $existingEmployee) {
            session()->forget('google_pending_signup');
            if ($existingEmployee) {
                Auth::guard('employees')->login($existingEmployee);
                return redirect()->route('software.dashboard')->with('info', 'Your account is already registered. Logged in successfully!');
            }
            return redirect()->route('software.login')->with('error', 'An account with this email already exists. Please log in.');
        }

        // Finalize Registration in DB
        DB::beginTransaction();
        try {
            $name = $pending['name'];

            // Default 7-day plan
            $plan_data = PlanMaster::where('status', 'active')
                ->where(function ($query) {
                    $query->where('plan_valid_day', 7)
                        ->orWhere('name', 'LIKE', '%7%');
                })->first();

            if (!$plan_data) {
                $plan_data = PlanMaster::where('status', 'active')->first();
            }

            $plan_from = date('Y-m-d');
            $plan_to = Carbon::now()->addDays((int) ($plan_data?->plan_valid_day ?? 7))->format('Y-m-d');

            // Sanitize Company Name & App Key (XSS/Script injection protection)
            $companyName = strip_tags(trim($request->company_name));
            if (empty($companyName)) {
                $companyName = strip_tags(trim($name));
            }
            $cleanName = preg_replace('/[^A-Za-z0-9]/', '', $companyName);
            if (strlen($cleanName) < 3) {
                $cleanName = str_pad($cleanName, 3, 'X');
            }
            $prefix = ucfirst(strtolower(substr($cleanName, 0, 3)));
            $appKey = $prefix . "@" . date("Y");

            // Auto-generate password & user-provided WhatsApp number
            $plainPassword = Str::random(10) . '1@';
            $whatsappNumber = preg_replace('/[^0-9]/', '', trim($request->whatsapp_number));

            $companyData = [
                'company_name' => $companyName,
                'person_name' => $name,
                'email' => $email,
                'whatsapp_number' => $whatsappNumber,
                'password' => Hash::make($plainPassword),
                'sp' => Helper::generateSP($plainPassword),
                'otp' => $pending['otp'] ?? '',
                'app_key' => $appKey,
                'phonecode' => '91',
                'country_id' => null,
                'state_id' => null,
                'city_id' => null,
                'plan_id' => $plan_data?->id ?? 1,
                'plan_from' => $plan_from,
                'plan_to' => $plan_to,
                'date_format' => Helper::getDefaultDateFormat(),
                'time_format' => Helper::getDefaultTimeFormat(),
                'hra_percentage' => 40,
                'max_employee_user_count' => $plan_data?->max_employee_user_count ?? 10,
                'app_right' => $plan_data?->app_right ?? [],
                'panel_right' => $plan_data?->panel_right ?? [],
                'panel_url' => url('/software/login'),
                'mobile_min' => 10,
                'mobile_max' => 10,
                'branch_type' => 'single',
                'status' => 'active',
                'register_type' => 'google',
                'created_by' => 0,
            ];

            // 1. Create Active Company
            $company = Company::create($companyData);
            $company_id = $company->id;

            // 2. Create CompanyDetails
            CompanyDetails::create(['company_id' => $company_id]);

            // 3. Create TeamRole
            $team_role = TeamRole::create([
                'company_id' => $company_id,
                'parent_id' => '0',
                'name' => $companyName,
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

            // 5. Create Super-Admin Employee
            $employee = Employee::create([
                'company_id' => $company_id,
                'branch_id' => 0,
                'parent_id' => 0,
                'parent_type_id' => 0,
                'first_name' => $name,
                'full_name' => $name,
                'name' => $name,
                'username' => $whatsappNumber ?: $email,
                'mobile_no' => $whatsappNumber,
                'password' => Hash::make($plainPassword),
                'sp' => Helper::generateSP($plainPassword),
                'country_id' => null,
                'state_id' => null,
                'city_id' => null,
                'email' => $email,
                'contact_number' => $whatsappNumber,
                'other_number' => $whatsappNumber,
                'employee_code' => 'EMP-001',
                'role_id' => $team_role->id,
                'status' => 'active',
                'created_by' => 0,
            ]);

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
            CompanySubscriptionPlan::create([
                'company_id' => $company_id,
                'plan_id' => $plan_data?->id ?? 1,
                'plan_from' => $plan_from,
                'plan_to' => $plan_to,
                'extra_detail' => json_encode($plan_data ? $plan_data->toArray() : []),
                'plan_expiry_date' => $plan_to,
                'subscription_status' => 'active',
                'created_by' => 0,
            ]);

            // 10. Audit Record
            CompanyRegistration::create([
                'company_name' => $companyName,
                'person_name' => $name,
                'whatsapp_number' => $whatsappNumber,
                'email' => $email,
                'password' => Hash::make($plainPassword),
                'sp' => Helper::generateSP($plainPassword),
                'otp' => $pending['otp'] ?? '',
                'country_id' => null,
                'state_id' => null,
                'city_id' => null,
                'plan_id' => $plan_data?->id ?? 1,
                'date_format' => Helper::getDefaultDateFormat(),
                'time_format' => Helper::getDefaultTimeFormat(),
                'hra_percentage' => 40,
                'status' => 'active',
                'register_type' => 'google',
            ]);

            DB::commit();

            // Clear pending session
            session()->forget('google_pending_signup');

            // Send Welcome Credentials Email
            try {
                $loginUrl = 'https://oceanhr.in/hrms/software/login';
                $subject = "Welcome to OceanHR - Your Login Credentials";
                $logoUrl = 'https://oceanhr.in/hrms/public/software/img/logo.png';

                $htmlContent = '
                <!-- Notification & Preheader Preview Snippet -->
                <div style="display:none;font-size:1px;color:#ffffff;line-height:1px;max-height:0px;max-width:0px;opacity:0;overflow:hidden;mso-hide:all;">
                    Welcome to OceanHR! Your login credentials: App Key: ' . htmlspecialchars($appKey) . ' | Username: ' . htmlspecialchars($whatsappNumber) . '.
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
                        <h3 style="color: #0f172a; margin-top: 0; font-size: 20px; font-weight: 700;">Welcome, ' . htmlspecialchars($name) . '! 👋</h3>
                        <p style="color: #475569; font-size: 15px; line-height: 1.6; margin-bottom: 25px;">
                            Thank you for registering <strong>' . htmlspecialchars($companyName) . '</strong> with <strong>OceanHR</strong> via Google. Your company account has been created successfully.
                        </p>

                        <!-- Credentials Box -->
                        <div style="background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%); border: 1px solid #cbd5e1; border-left: 4px solid #266BEE; border-radius: 10px; padding: 20px; margin-bottom: 25px;">
                            <h4 style="margin: 0 0 15px 0; color: #1e293b; font-size: 16px; font-weight: 700;">
                                🔑 Your Account Login Credentials:
                            </h4>
                            <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                                <tr style="border-bottom: 1px dashed #cbd5e1;">
                                    <td style="padding: 10px 0; color: #475569; font-weight: 600; width: 160px;">App Key:</td>
                                    <td style="padding: 10px 0; color: #000000; font-family: Arial, Helvetica, sans-serif; font-size: 15px; font-weight: 800;">' . htmlspecialchars($appKey) . '</td>
                                </tr>
                                <tr style="border-bottom: 1px dashed #cbd5e1;">
                                    <td style="padding: 10px 0; color: #475569; font-weight: 600;">Username / Email:</td>
                                    <td style="padding: 10px 0; color: #000000; font-weight: 700;">' . htmlspecialchars($whatsappNumber) . ' <span style="color: #94a3b8; font-weight: 400; margin: 0 4px;">|</span> <span style="color: #000000; text-decoration: none;">' . htmlspecialchars($email) . '</span></td>
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

                Mail::to($email)->send(new GenericMail($subject, $htmlContent));
            } catch (\Exception $mailEx) {
                Log::error("Google Welcome Email Error: " . $mailEx->getMessage());
            }

            // Auto Log in the newly registered Employee
            Auth::guard('employees')->login($employee, true);

            session()->flash('success', 'Company registered successfully! Login credentials have been sent to your email (' . $email . ').');
            session()->save();

            return redirect()->route('software.dashboard');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Google Registration Error: " . $e->getMessage());
            return redirect()->back()->with('error', 'Registration failed: ' . $e->getMessage());
        }
    }

    /**
     * Resend OTP Code
     */
    public function resendOtp()
    {
        $pending = session('google_pending_signup');

        if (!$pending || empty($pending['email'])) {
            return redirect()->route('software.login')->with('error', 'Session expired. Please try Google sign-up again.');
        }

        $otp = (string) rand(100000, 999999);

        $this->sendOtpEmail($pending['email'], $pending['name'], $otp);

        $pending['otp'] = $otp;
        $pending['otp_expires_at'] = Carbon::now()->addMinutes(2)->timestamp;
        session(['google_pending_signup' => $pending]);

        return redirect()->back()->with('success', 'A new OTP code has been sent to your email.');
    }

    /**
     * Helper to send OTP email
     */
    private function sendOtpEmail($email, $name, $otp)
    {
        try {
            $logoUrl = 'https://oceanhr.in/hrms/public/software/img/logo.png';
            $subject = "OceanHR - Verify Email OTP for Registration";
            $uniqueId = substr(md5(uniqid(mt_rand(), true)), 0, 8);
            $htmlContent = '
            <!-- Notification & Preheader Preview Snippet -->
            <div style="display:none;font-size:1px;color:#ffffff;line-height:1px;max-height:0px;max-width:0px;opacity:0;overflow:hidden;mso-hide:all;">
                Your OceanHR 6-digit verification code is ' . $otp . '. Please use this OTP to complete your Google registration.
            </div>
            <div style="display:none;max-height:0px;overflow:hidden;mso-hide:all;">
                &#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;
            </div>

            <div style="font-family: \'Segoe UI\', Helvetica, Arial, sans-serif; max-width: 600px; margin: 30px auto; background-color: #ffffff; border-radius: 14px; box-shadow: 0 10px 25px rgba(0,0,0,0.08); overflow: hidden; border: 1px solid #e2e8f0;">
                <div style="text-align: center; padding: 30px 20px 15px; background-color: #ffffff; border-bottom: 2px solid #f1f5f9;">
                    <img src="' . $logoUrl . '" alt="OceanHR Logo" style="max-height: 60px; width: auto; display: block; margin: 0 auto 14px;" />
                    <div style="display: inline-block; background-color: #eff6ff; color: #1d4ed8; font-size: 13px; font-weight: 800; padding: 7px 20px; border-radius: 25px; border: 1.5px solid #93c5fd; text-transform: uppercase; letter-spacing: 0.8px;">
                        🔑 Email Verification Code
                    </div>
                </div>
                <div style="padding: 30px 25px; background-color: #ffffff; text-align: center;">
                    <h3 style="color: #0f172a; margin-top: 0; font-size: 20px; font-weight: 700;">Hello ' . htmlspecialchars($name) . ',</h3>
                    <p style="color: #475569; font-size: 15px; line-height: 1.6; margin-bottom: 25px;">
                        Please use the following 6-digit One Time Password (OTP) to complete your Google Sign-Up for <strong>OceanHR</strong>:
                    </p>

                    <div style="display: inline-block; background-color: #eff6ff; color: #1d4ed8; font-family: \'Courier New\', Courier, monospace; font-size: 34px; font-weight: 800; letter-spacing: 8px; padding: 14px 35px; border-radius: 12px; margin-bottom: 25px; border: 1.5px solid #93c5fd; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.08);">
                        ' . $otp . '
                    </div>

                    <p style="color: #94a3b8; font-size: 13px; margin: 0;">This OTP is valid for 2 minutes. Please do not share this code with anyone.</p>
                </div>
                <div style="text-align: center; padding: 20px; background-color: #f8fafc; border-top: 1px solid #e2e8f0; color: #64748b; font-size: 12px; line-height: 1.6;">
                    <p style="margin: 0 0 4px 0; font-weight: 600; color: #475569;">Need Help? Contact Support at <a href="mailto:supportoceanhr@gmail.com" style="color: #266BEE; text-decoration: none;">supportoceanhr@gmail.com</a></p>
                    <p style="margin: 0;">© ' . date('Y') . ' Ocean Infotech. All Rights Reserved. <span style="display:none; font-size:0px; line-height:0px; max-height:0px; opacity:0; overflow:hidden;">[' . $uniqueId . ']</span></p>
                </div>
            </div>';

            Mail::to($email)->send(new GenericMail($subject, $htmlContent));
        } catch (\Exception $e) {
            Log::error("Google OTP Email Error: " . $e->getMessage());
        }
    }
}
