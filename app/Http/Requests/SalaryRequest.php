<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Salary;
use App\Models\Branch;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SalaryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(Request $request): array
    {
        $rules = [
            'company_id' => ['required', 'exists:companies,id'],
            'branch_id' => ['required', 'exists:branches,id'],
            'department_id' => ['required', 'exists:departments,id'],
            'employee_id' => ['required', 'exists:employees,id'],
            'year' => ['required'],
            'month' => ['required'],

            'added_date' => ['nullable', 'date'],
            'calculate_days' => ['nullable', 'numeric'],
            'total_present_day' => ['nullable', 'numeric'],
            'adjustment_days' => ['nullable', 'numeric'],
            'adjustment_remark' => ['nullable', 'string', 'max:255'],
            'half_day' => ['nullable', 'numeric'],
            'holiday' => ['nullable', 'numeric'],

            'compensation_hour' => ['nullable', 'numeric'],
            'compensation_day' => ['nullable', 'numeric'],
            'total_week_off' => ['nullable', 'numeric'],
            'total_sandwich_leave' => ['nullable', 'numeric'],
            'total_leave' => ['nullable', 'numeric'],
            'total_company_pay_leave' => ['nullable', 'numeric'],
            'total_employee_pay_leave' => ['nullable', 'numeric'],
            'total_absent' => ['nullable', 'numeric'],
            'total_day' => ['nullable', 'numeric'],

            'working_hour' => ['nullable', 'numeric'],
            'ctc' => ['nullable', 'numeric'],
            'gross_salary' => ['nullable', 'numeric'],
            'given_calculate_salary' => ['nullable', 'numeric'],
            'per_day_salary' => ['nullable', 'numeric'],

            'conveyance_allowance' => ['nullable', 'numeric'],
            'medical_allowance' => ['nullable', 'numeric'],
            'special_allowance' => ['nullable', 'numeric'],
            'present_day_amount' => ['nullable', 'numeric'],
            'employee_weekoff_amount' => ['nullable', 'numeric'],
            'company_pay_leave_amount' => ['nullable', 'numeric'],
            'employee_pay_leave_amount' => ['nullable', 'numeric'],

            // Fixed Salary Structure
            'fxs_basic' => ['nullable', 'numeric'],
            'fxs_hra' => ['nullable', 'numeric'],
            'fxs_other' => ['nullable', 'numeric'],
            'fxs_total_earning' => ['nullable', 'numeric'],

            // Daily Wage Salary
            'dws_basic' => ['nullable', 'numeric'],
            'dws_da' => ['nullable', 'numeric'],
            'dws_other' => ['nullable', 'numeric'],
            'dws_total_earning' => ['nullable', 'numeric'],

            // OT and Performance
            'actual_total_ot_hours' => ['nullable', 'numeric'],
            'earn_ot_hours' => ['nullable', 'numeric'],
            'earn_ot_payable_amt' => ['nullable', 'numeric'],
            'earn_ot_days' => ['nullable', 'numeric'],
            'earn_performation_incentive' => ['nullable', 'numeric'],

            // Bonus
            'bonus_amount' => ['nullable', 'numeric'],
            'bonus_temp_amount' => ['nullable', 'numeric'],
            'bonus_amount_adjustment' => ['nullable', 'numeric'],

            // Earnings
            'earn_sub_total' => ['nullable', 'numeric'],
            'total_earning' => ['nullable', 'numeric'],

            // Deductions
            'ded_employee_pf' => ['nullable', 'numeric'],
            'ded_pradhan_mantri_pf' => ['nullable', 'numeric'],
            'ded_esi_employee' => ['nullable', 'numeric'],
            'ded_esi_company' => ['nullable', 'numeric'],
            'ded_pt' => ['nullable', 'numeric'],
            'ded_insurance' => ['nullable', 'numeric'],
            'ded_advance' => ['nullable', 'numeric'],
            'advance_amount_adjustment' => ['nullable', 'numeric'],
            'ded_tds' => ['nullable', 'numeric'],
            'tds_amount_adjustment' => ['nullable', 'numeric'],
            'ded_wf' => ['nullable', 'numeric'],
            'ded_loan_amount' => ['nullable', 'numeric'],
            'loan_amount_adjustment' => ['nullable', 'numeric'],
            'ded_other' => ['nullable', 'numeric'],
            'ded_other_remark' => ['nullable', 'string', 'max:255'],

            // Totals
            'total_deduction' => ['nullable', 'numeric'],
            'net_bank_pay' => ['nullable', 'numeric'],

            // OT Adjustments
            'additional_ot_hour' => ['nullable', 'numeric'],
            'total_acc_ot_plus_add_ot' => ['nullable', 'numeric'],
        ];

        return $rules;
    }
}
