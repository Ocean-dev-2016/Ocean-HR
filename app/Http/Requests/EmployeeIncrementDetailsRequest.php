<?php

namespace App\Http\Requests;

use App\Models\Company;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\EmployeeIncrementDetails;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmployeeIncrementDetailsRequest extends FormRequest
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

        $employee_increment_details = $request->route('employee_increment_detail');

        $id = 0;
        if ($employee_increment_details instanceof EmployeeIncrementDetails) {
            $id = $employee_increment_details->id;
        } elseif ($request->has('edit_id')) {
            $id = $request->input('edit_id');
        }

        return [
            'company_id' => ['required', Rule::exists((new Company())->getTable(), 'id')],
            'employee_id' => ['required', Rule::exists((new Employee())->getTable(), 'id')],
            'icrement_date'        => ['required', 'date'],
            'basic_da'             => ['required', 'numeric'],
            'hra'                  => ['nullable', 'numeric'],
            'conveyance_allowance' => ['nullable', 'numeric'],
            'medical_allowance'    => ['nullable', 'numeric'],
            'special_allowance'    => ['nullable', 'numeric'],
            'pf'                   => ['nullable', 'numeric'],

            'effective_month'       => ['required'],
            'effective_year'        => ['required'],
            'designation_id' => ['required', Rule::exists((new Designation())->getTable(), 'id')],
            'per_day_salary'        => ['nullable', 'numeric'],
            'per_hour_salary'       => ['nullable', 'numeric'],
            'remark'                => ['nullable'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }
}
