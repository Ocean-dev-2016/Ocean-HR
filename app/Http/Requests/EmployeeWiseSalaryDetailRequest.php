<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Company;
use App\Models\Employee;
use App\Models\EmployeeWiseSalaryDetail;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmployeeWiseSalaryDetailRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $nullableNumericFields = [
            'pf_percentage',
            'pradhanmantri_pf_percentage',
            'tds_percentage',
            'insurance_amount',
            'pt_amount',
            'esi_company_side_percentage',
            'esi_employee_side_percentage',
            'basic_da',
            'hra',
            'conveyance_allowance',
            'medical_allowance',
            'special_allowance',
            'ctc',
        ];

        foreach ($nullableNumericFields as $field) {
            if ($this->has($field) && $this->input($field) === '') {
                $this->merge([$field => null]);
            }
        }

        if ($this->has('week_off') && is_string($this->input('week_off'))) {
            $decoded = json_decode($this->input('week_off'), true);
            if (is_array($decoded)) {
                $this->merge(['week_off' => $decoded]);
            }
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(Request $request): array
    {

        $employee_salary_wise_detail = $request->route('employee-wise-salary-details');

        $id = 0;
        if ($employee_salary_wise_detail instanceof EmployeeWiseSalaryDetail) {
            $id = $employee_salary_wise_detail->id;
        } elseif ($request->has('edit_id')) {
            $id = $request->input('edit_id');
        }

        $weekDays = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $salaryClassifications = ['PHS', 'PDS', 'PMS', 'PWS'];
        $salaryMonthCounts = ['Fix 30 Days', 'Per Month Total Days', 'Per Month Total Days - Week Off'];
        $pfTypes = ['NO-PF', 'PF-ABRY', 'COMPANY GIVE BOTH SIDE PF', 'EMPLOYEE'];
        $sandwichAppliedOn = ['Both', 'Week Off', 'Holiday'];
        $sandwichTypes = ['Full Sandwich', 'Half Sandwich With Pay', 'Half Sandwich With Deduct'];

        $rules = [
            'company_id' => ['required', Rule::exists((new Company())->getTable(), 'id')],
            'employee_id' => ['required', Rule::exists((new Employee())->getTable(), 'id')],
            'salary_classification' => ['required', Rule::in($salaryClassifications)],
            'week_off' => ['required', 'array', 'min:1'],
            'week_off.*' => ['string', Rule::in($weekDays)],
            'overtime' => ['required', 'in:0,1'],
            'is_welfare_fund_applied' => ['required', 'in:0,1'],
            'welfare_fund_amount' => ['nullable', 'numeric', 'min:0', 'required_if:is_welfare_fund_applied,1'],
            'leave_elegiblity' => ['required', 'in:0,1'],
            'sandwich_rule_flag' => ['required', 'in:0,1'],
            'sandwich_rule_applied_on' => ['nullable', Rule::in($sandwichAppliedOn), 'required_if:sandwich_rule_flag,1'],
            'sandwich_rule_type' => ['nullable', Rule::in($sandwichTypes), 'required_if:sandwich_rule_flag,1'],
            'is_bonus_applied' => ['required', 'in:0,1'],
            'salary_calculation_month_count' => ['nullable', Rule::in($salaryMonthCounts)],
            'pf_type' => ['nullable', Rule::in($pfTypes)],
            'pf' => ['required', 'in:0,1'],
            'pf_percentage' => ['nullable', 'numeric', 'min:0', 'max:100', 'required_if:pf,1'],
            'pradhanmantri_pf' => ['required', 'in:0,1'],
            'pradhanmantri_pf_percentage' => ['nullable', 'numeric', 'min:0', 'max:100', 'required_if:pradhanmantri_pf,1'],
            'tds' => ['required', 'in:0,1'],
            'tds_percentage' => ['nullable', 'numeric', 'min:0', 'max:100', 'required_if:tds,1'],
            'insurance' => ['required', 'in:0,1'],
            'insurance_amount' => ['nullable', 'numeric', 'min:0', 'required_if:insurance,1'],
            'pt' => ['required', 'in:0,1'],
            'pt_amount' => ['nullable', 'numeric', 'min:0', 'required_if:pt,1'],
            'is_esi_company_side' => ['required', 'in:0,1'],
            'esi_company_side_percentage' => ['nullable', 'numeric', 'min:0', 'max:100', 'required_if:is_esi_company_side,1'],
            'esi_employee_side' => ['required', 'in:0,1'],
            'esi_employee_side_percentage' => ['nullable', 'numeric', 'min:0', 'max:100', 'required_if:esi_employee_side,1'],
            'gratuity_calculation' => ['required', 'in:0,1'],
            'basic_da' => ['required', 'numeric', 'min:0'],
            'hra' => ['nullable', 'numeric', 'min:0'],
            'conveyance_allowance' => ['nullable', 'numeric', 'min:0'],
            'medical_allowance' => ['nullable', 'numeric', 'min:0'],
            'special_allowance' => ['nullable', 'numeric', 'min:0'],
            'ctc' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
        ];

        // dd("L-77",$rules);
        return $rules;
    }
}
