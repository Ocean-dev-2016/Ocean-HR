<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Company;
use App\Helpers\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class EmployeeAuthController extends Controller
{
    /**
     * Employee Login API
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        try {
            // 1. Validation
            $validator = Validator::make($request->all(), [
                'app_key' => 'required',
                'username' => 'required',
                'password' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors()->all(), [], 422);
            }

            // 2. Check App Key and Company Status
            $company = Company::where('app_key', $request->app_key)
                ->where('status', 'active')
                ->first();

            if (!$company) {
                return $this->sendError('Invalid App Key or Company is inactive.', [], [], 401);
            }

            // 3. Find Employee by username, email or contact_number
            $employee = Employee::where('company_id', $company->id)
                ->where(function ($query) use ($request) {
                    $query->where('username', $request->username)
                        ->orWhere('email', $request->username)
                        ->orWhere('contact_number', $request->username);
                })
                ->first();

            if (!$employee) {
                return $this->sendError('User not found.', [], [], 404);
            }

            // 4. Verify Password
            if (!Hash::check($request->password, $employee->password)) {
                return $this->sendError('Invalid credentials.', [], [], 401);
            }

            // 5. Check if employee is active
            if ($employee->status !== 'active') {
                return $this->sendError('Your account is inactive. Please contact admin.', [], [], 403);
            }

            // 6. Revoke existing tokens for multi-device login restriction (Kick other devices)
            $employee->tokens()->update(['revoked' => true]);

            // 7. Save device_token if provided (for push notifications)
            if ($request->filled('device_token')) {
                $employee->device_token = $request->device_token;
                $employee->save();
            } elseif ($request->filled('fcm_token')) {
                $employee->device_token = $request->fcm_token;
                $employee->save();
            }

            // 8. Generate Token (Passport)
            $token = $employee->createToken('EmployeeAuth')->accessToken;

            // 9. Prepare Response Data
            $responseData = [
                'id' => $employee->id,
                'company_id' => $employee->company_id,
                'branch_id' => $employee->branch_id,
                'full_name' => $employee->full_name,
                'email' => $employee->email,
                'contact_number' => $employee->contact_number,
                'username' => $employee->username,
                'employee_code' => $employee->employee_code,
                'token' => $token,
                'device_token' => $employee->device_token,
            ];

            return $this->sendResponse($responseData, 'Login successful.');

        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', [$e->getMessage()], [], 500);
        }
    }

    /**
     * Get Employee Profile API
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProfile(Request $request)
    {
        try {
            $employee = $request->user();

            if (!$employee) {
                return $this->sendError('Unauthorized.', [], [], 401);
            }

            return $this->sendResponse($this->getProfileResponseData($employee), 'Profile retrieved successfully.');

        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', [$e->getMessage()], [], 500);
        }
    }

    /**
     * Update Employee Profile API
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfile(Request $request)
    {
        try {
            $employee = $request->user();
            if (!$employee) {
                return $this->sendError('Unauthorized.', [], [], 401);
            }

            $validator = Validator::make($request->all(), [
                'full_name' => 'required|string|max:255',
                'email' => [
                    'required',
                    'email',
                    Rule::unique('employees', 'email')->ignore($employee->id)->where(function ($query) use ($employee) {
                        return $query->where('company_id', $employee->company_id)->whereNull('deleted_at');
                    }),
                ],
                'contact_number' => [
                    'required',
                    'string',
                    Rule::unique('employees', 'contact_number')->ignore($employee->id)->where(function ($query) use ($employee) {
                        return $query->where('company_id', $employee->company_id)->whereNull('deleted_at');
                    }),
                ],
                'date_of_birth' => 'required|date|date_format:d-m-Y|before:today',
                'gender' => 'required|in:Male,Female,Other',
                'device_token' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors()->all(), [], 422);
            }

            $updateData = [
                'full_name' => $request->full_name,
                'email' => $request->email,
                'contact_number' => $request->contact_number,
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
            ];

            if ($request->filled('device_token')) {
                $updateData['device_token'] = $request->device_token;
            } elseif ($request->filled('fcm_token')) {
                $updateData['device_token'] = $request->fcm_token;
            }

            $employee->update($updateData);

            return $this->sendResponse($this->getProfileResponseData($employee->fresh()), 'Profile updated successfully.');

        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', [$e->getMessage()], [], 500);
        }
    }

    /**
     * Employee Logout API
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        try {
            $employee = $request->user('employee-api');
            if ($employee && $employee->token()) {
                $employee->token()->revoke();
                return $this->sendResponse([], 'Logged out successfully.');
            }
            return $this->sendError('Unauthorized: Token potentially missing or already revoked.', [], [], 401);
        } catch (\Exception $e) {
            return $this->sendError('Logout Execution Error', [$e->getMessage()], [], 500);
        }
    }

    /**
     * Helper to format profile response data
     * 
     * @param Employee $employee
     * @return array
     */
    private function getProfileResponseData($employee)
    {
        return [
            'id' => $employee->id . '',
            'company_id' => $employee->company_id . '',
            'branch_id' => $employee->branch_id . '',
            'full_name' => $employee->full_name . '',
            'email' => $employee->email . '',
            'contact_number' => $employee->contact_number . '',
            'username' => $employee->username . '',
            'employee_code' => $employee->employee_code . '',
            'date_of_birth' => $employee->date_of_birth . '',
            'gender' => $employee->gender . '',
            'status' => $employee->status . '',
        ];
    }
}
