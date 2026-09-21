<?php
//EmployeeRequest
namespace App\Http\Requests;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Employee;
use App\Models\MasterCity;
use App\Models\MasterCountry;
use App\Models\MasterState;
use App\Rules\ValidAadhaarNumber;
use App\Rules\ValidPanCardNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;


class EmployeeRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        // if full_name is null/empty, build it from other fields
        if (empty($this->full_name) && false) {
            $parts = [];

            if (!empty($this->first_name)) {
                $parts[] = $this->first_name;
            }

            if (!empty($this->middle_name)) {
                $parts[] = $this->middle_name;
            }

            if (!empty($this->father_name)) {
                $parts[] = $this->father_name;
            }

            $this->merge([
                'full_name' => implode(' ', $parts),
            ]);
        }

        if ($this->has('ifsc_code') && !empty($this->ifsc_code)) {
            $this->merge([
                'ifsc_code' => strtoupper(trim(str_replace(' ', '', $this->ifsc_code))),
            ]);
        }
    }

    public function rules(Request $request): array
    {
        $routeParam = $request->route('employee') ?? $request->route('employees');
        $id = is_object($routeParam) ? $routeParam->id : ($routeParam ?? $request->edit_id ?? 0);

        // Check company's employee_code_auto_generation setting
        $company = Company::find($request->company_id);
        $isManualEmployeeCode = $company && $company->employee_code_auto_generation === 'manual';

        $rules = [
            'company_id' => ['required', Rule::exists((new Company())->getTable(), 'id')],
            'branch_id' => ['nullable', Rule::exists((new Branch())->getTable(), 'id')],
            'employee_code' => [
                $isManualEmployeeCode ? 'required' : 'nullable',
                'string',
                'max:255',
                Rule::unique((new Employee())->getTable(), 'employee_code')
                    ->ignore($id)
                    ->where(fn($q) => $q->where('company_id', $request->company_id)->whereNull('deleted_at')),
            ],
            'biometric_user_id' => [
                'nullable',
                'string',
                'max:50',
                function ($attribute, $value, $fail) use ($request, $id, $company) {
                    // Only validate uniqueness if biometric_user_id is not NULL
                    if ($value !== null && $value !== '') {
                        $exists = Employee::where('biometric_user_id', $value)
                            ->where('company_id', $request->company_id)
                            ->where(function ($query) use ($request) {
                                if ($request->branch_id) {
                                    $query->where('branch_id', $request->branch_id);
                                } else {
                                    $query->whereNull('branch_id');
                                }
                            })
                            ->when($id, function ($query) use ($id) {
                                $query->where('id', '!=', $id);
                            })
                            ->whereNull('deleted_at')
                            ->exists();

                        if ($exists) {

                            if ($company?->branch_type == 'single') {
                                $fail('The biometric user ID has already been taken for this company.');
                            } else if ($company?->branch_type == 'multiple') {
                                $fail('The biometric user ID has already been taken for this company and branch.');
                            } else {
                                $fail('The biometric user ID has already been taken for this company.');
                            }
                        }
                    }
                },
            ],
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['required', 'string', 'max:255'],
            'father_name' => ['required', 'string', 'max:255'],
            'full_name' => [
                'required',
                'string',
                Rule::unique((new Employee())->getTable(), 'full_name')
                    ->ignore($id)
                    ->where(fn($q) => $q->where('branch_id', $request->branch_id)->whereNull('deleted_at')),
            ],
            'gender' => ['required', 'in:' . implode(",", array_keys(config('constants.genders')))],
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique((new Employee())->getTable(), 'username')
                    ->ignore($id)
                    ->where(fn($q) => $q->where('company_id', $request->company_id)->whereNull('deleted_at')),
            ],
            'password' => [
                $id ? 'nullable' : 'required',
                'string',
                'min:6',
                'max:255',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).+$/',
            ],
            'country_id' => ['required', 'exists:' . (new MasterCountry())->getTable() . ',id'],
            'state_id' => ['required', 'exists:' . (new MasterState())->getTable() . ',id'],
            'city_id' => ['required', 'exists:' . (new MasterCity())->getTable() . ',id'],
            'current_address' => ['required'],
            'permanent_address' => ['required'],
            'aadhar_card_number' => [
                'required',
                new ValidAadhaarNumber(),
                Rule::unique((new Employee())->getTable(), 'aadhar_card_number')->ignore($id, 'id')->whereNull('deleted_at'),
            ],
            'pan_card_number' => [
                'nullable',
                new ValidPanCardNumber(),
                Rule::unique('employees', 'pan_card_number')->ignore($id, 'id')->whereNull('deleted_at'),
            ],

            // Optional fields (not in required list)
            'role_id' => ['nullable', 'string', 'max:255'],
            'email' => [
                'nullable',
                'email',
                Rule::unique((new Employee())->getTable(), 'email')
                    ->ignore($id)
                    ->where(fn($q) => $q->where('company_id', $request->company_id)->whereNull('deleted_at')),
            ],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'contact_number' => ['nullable', 'numeric', 'digits:10'],
            'other_number' => ['nullable', 'numeric', 'digits:10'],
            'date_of_anniversary' => ['nullable', 'date', 'before_or_equal:today'],
            'bank_account_number' => ['nullable', 'string', 'digits_between:9,18'],
            'ifsc_code' => ['nullable', 'string', 'size:11'],
            'status' => ['required', 'in:active,inactive'],
        ];

        // dd("L-118", $rules, $id, $request->all(), $request->route('employees'), $request->route()->parameters());
        return $rules;
    }


    public function messages(): array
    {
        return [
            'password.regex' => 'A :attribute must include at least 1 uppercase, 1 lowercase, 1 number, and 1 special character (@$!%*?&).',
            'ifsc_code.size' => 'The IFSC Code must be exactly 11 characters.',
        ];
    }
}
