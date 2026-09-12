<?php

namespace App\Http\Controllers\software;

use App\Http\Controllers\Controller;
use App\Models\AdminSoftware;
use App\Models\Company;
use App\Models\Employee;
use App\Models\CompanySubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
