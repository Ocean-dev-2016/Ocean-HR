<?php

namespace App\Http\Controllers;

use App\Models\TeamAttendance;
use App\Models\CompanySubscriptionPlan;
use App\Models\Company;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Gate;
use App\Models\Employee;
use DateTime;
use Carbon\Carbon;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected static $employeeNamesCache = [];

    public $authenticateLoginUserDetails = null;
    public $currentGuard = null;
    public $companyData = null;
    public function __construct()
    {
        $this->middleware(function ($request, $next) {

            foreach (array_keys(config('auth.guards')) as $kry => $guard) {
                if (Auth::guard($guard)->check()) {
                    $this->currentGuard = $guard;
                    break;
                }
            }

            $this->authenticateLoginUserDetails = (Auth::guard('admin_software')->check()) ? Auth::guard('admin_software')?->user() : null;

            // For admin_software users, check if a company is selected from session
            $selectedCompanyId = session('selected_company_id');
            if ($this->authenticateLoginUserDetails && Auth::guard('admin_software')->check() && $selectedCompanyId) {
                // Create a modified user object with selected company_id for admin
                $this->authenticateLoginUserDetails = clone $this->authenticateLoginUserDetails;
                $this->authenticateLoginUserDetails->company_id = $selectedCompanyId;
            }

            if (!$this->authenticateLoginUserDetails) {
                $this->authenticateLoginUserDetails = (Auth::guard('employees')->check()) ? Auth::guard('employees')?->user() : null;

                // For employee guard, auto-set their company_id in session
                if ($this->authenticateLoginUserDetails && Auth::guard('employees')->check()) {
                    $employeeCompanyId = $this->authenticateLoginUserDetails->company_id;
                    if ($employeeCompanyId) {
                        session(['selected_company_id' => $employeeCompanyId]);
                        $selectedCompanyId = $employeeCompanyId;
                    }
                }
            }

            // Share selected company info for views
            View::share('selectedCompanyId', $selectedCompanyId);
            View::share('authenticateLoginUserDetails', $this->authenticateLoginUserDetails);

            // Do something with $this->user, like load user-specific settings
            View::share('currentGuard', $this->currentGuard);
            if ($this->currentGuard === 'employees') {
                $user = Auth::guard('employees')->user();
                $company_id = $user?->company_id;
                $parent_type_id = $user?->parent_type_id;

                View::share('parent_type_id', $parent_type_id);
                if ($parent_type_id != 0) {
                    $attendance = TeamAttendance::where('company_id', $company_id)
                        ->where('employees_id', $user?->id)
                        ->whereNull('punch_out_time')
                        ->orderByDesc('id')
                        ->first();

                    $punchType = $attendance ? 'out' : 'in';
                    View::share('punchType', $punchType);
                    $mainAttendanceAddBtn = (isset($company_id) && !$company_id) ? true : Gate::check('hasPermission', [config('constants.permissions.add'), 'Team Attendance']);
                    View::share('mainAttendanceAddBtn', $mainAttendanceAddBtn);
                    if ($attendance) {

                        $date = new DateTime($attendance->punch_in_time);
                        $punch_in_time_formatted = $date->format('d-m-Y h:i A');
                        View::share('punchInTime', $punch_in_time_formatted);
                    }
                }

                $companyData = Company::with('plan')->where('id', $company_id)->first();
                if (!$companyData) {
                    Auth::guard('employees')->logout();
                    if ($request->hasSession()) {
                        $request->session()->invalidate();
                    }
                    return redirect()->route('software.login')->with('error', 'Associated company account not found. Please log in again.');
                }
                if (isset($companyData->plan_id) && !empty($companyData->plan_id)) {
                    $latestPlan = CompanySubscriptionPlan::where('company_id', $company_id)->where('plan_id', $companyData->plan_id)->orderBy('id', 'desc')->first();
                    $expiryDate = Carbon::parse($latestPlan?->plan_expiry_date);
                    if ($latestPlan && !empty($expiryDate) && $expiryDate->between(Carbon::today(), Carbon::today()->addDays(10))) {
                        View::share('is_plan_expired', true);
                        View::share('mainPlanExpiryDate', $expiryDate);
                        View::share('mainPlanName', $companyData->plan?->name);
                    }
                    View::share('companyData', $companyData);
                }
                if (isset($companyData->branch_type) && !empty($companyData->branch_type)) {
                    View::share('branch_type', $companyData->branch_type);
                }
                $this->companyData = $companyData;
            }
            $company_id = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails?->company_id : null;

            return $next($request);
        });
    }

    public function sendResponse($result, $message, $extraData = [], $code = 200)
    {
        // Only apply recursive date formatting for API requests to avoid affecting web AJAX calls
        if (request()->is('api/*')) {
            $result = $this->formatDatesRecursively($result);
        }

        $response = [
            'status' => true,
            'status_code' => $code,
            'message' => $message,
            'data' => $result
        ];

        if (count($extraData) > 0) {
            if (request()->is('api/*')) {
                $extraData = $this->formatDatesRecursively($extraData);
            }
            $response['extraData'] = $extraData;
        }

        return response()->json($response, $code);
    }

    /**
     * Recursively format date and time strings in the result array/collection/object.
     * Only applies if the request is an API request.
     * 
     * @param mixed $data
     * @return mixed
     */
    private function formatDatesRecursively($data)
    {
        // Handle Eloquent Models (convert to array first)
        if ($data instanceof \Illuminate\Database\Eloquent\Model) {
            return $this->formatDatesRecursively($data->toArray());
        }

        // Handle Collections
        if ($data instanceof \Illuminate\Support\Collection) {
            return $data->map(fn($item) => $this->formatDatesRecursively($item));
        }

        // Handle Pagination (LengthAwarePaginator, etc.)
        if ($data instanceof \Illuminate\Contracts\Pagination\Paginator || $data instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            $data->getCollection()->transform(fn($item) => $this->formatDatesRecursively($item));
            return $data;
        }

        // Handle Objects (stdClass)
        if (is_object($data) && $data instanceof \stdClass) {
            // Check for various employee ID variants and inject employee_name
            $employeeIdKeys = ['employee_id', 'employees_id', 'team_person_id', 'created_by', 'updated_by'];
            foreach ($employeeIdKeys as $key) {
                if (isset($data->$key) && !empty($data->$key) && !isset($data->employee_name)) {
                    // Specific logic for created_by/updated_by: maybe add creator_name?
                    // For now, if the user specifically asked for "Employee name", and these are employee IDs, we populate it.
                    // If multiple exist, the first one found wins for the 'employee_name' field.
                    $data->employee_name = $this->getEmployeeNameFromCache($data->$key);
                    break; 
                }
            }

            foreach (get_object_vars($data) as $key => $value) {
                $data->$key = $this->formatDatesRecursively($value);
            }
            return $data;
        }

        // Handle Arrays
        if (is_array($data)) {
            // Check for various employee ID variants and inject employee_name
            $employeeIdKeys = ['employee_id', 'employees_id', 'team_person_id', 'created_by', 'updated_by'];
            foreach ($employeeIdKeys as $key) {
                if (isset($data[$key]) && !empty($data[$key]) && !isset($data['employee_name'])) {
                    $data['employee_name'] = $this->getEmployeeNameFromCache($data[$key]);
                    break;
                }
            }

            foreach ($data as $key => $value) {
                $data[$key] = $this->formatDatesRecursively($value);
            }
            return $data;
        }

        // Handle DateTime strings
        if ($data instanceof \DateTimeInterface) {
            $carbon = Carbon::instance($data)->setTimezone(config('app.timezone', 'Asia/Kolkata'));
            if ($carbon->format('H:i:s') === '00:00:00') {
                return $carbon->format('d-m-Y');
            }
            return $carbon->format('d-m-Y h:i A');
        }

        if (is_string($data) && !empty($data) && strlen($data) >= 8) {
            // Match ISO-8601 (e.g., 2026-04-12T18:30:00.000000Z)
            if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $data)) {
                $carbon = Carbon::parse($data)->setTimezone(config('app.timezone', 'Asia/Kolkata'));
                if ($carbon->format('H:i:s') === '00:00:00') {
                    return $carbon->format('d-m-Y');
                }
                return $carbon->format('d-m-Y h:i A');
            }

            if (strlen($data) <= 19) {
                // Match Y-m-d H:i:s -> transform to d-m-Y h:i A (or just d-m-Y if midnight)
                if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $data)) {
                    $carbon = Carbon::parse($data)->setTimezone(config('app.timezone', 'Asia/Kolkata'));
                    if ($carbon->format('H:i:s') === '00:00:00') {
                        return $carbon->format('d-m-Y');
                    }
                    return $carbon->format('d-m-Y h:i A');
                }
                // Match Y-m-d -> transform to d-m-Y
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) {
                    return Carbon::parse($data)->format('d-m-Y');
                }
                // Match H:i:s (Time only) -> transform to h:i A
                if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $data)) {
                    return Carbon::parse($data)->format('h:i A');
                }
            }
        }

        return $data;
    }

    /**
     * Helper to get employee name from cache or DB
     */
    private function getEmployeeNameFromCache($employeeId)
    {
        if (isset(self::$employeeNamesCache[$employeeId])) {
            return self::$employeeNamesCache[$employeeId];
        }

        $employee = Employee::find($employeeId);
        $name = $employee ? $employee->proper_name : '-';
        self::$employeeNamesCache[$employeeId] = $name;

        return $name;
    }

    public function sendError($error, $errorMessages = [], $extraData = [], $code = 404)
    {
        $response = [
            'status' => false,
            'status_code' => $code,
            'message' => $error,
        ];

        if (!empty($errorMessages)) {
            $response['errors'] = $errorMessages;
        }

        if (count($extraData) > 0) {
            $response['extraData'] = $extraData;
        }

        return response()->json($response, $code);
    }
}
