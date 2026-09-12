<?php

namespace App\Http\Requests;

use App\Models\Company;
use App\Models\Employee;
use App\Models\EmploymentDetail;
use App\Models\EmployeeType;
use App\Models\Designation;
use App\Models\Department;
use App\Models\Process;
use App\Models\Shift;
use App\Models\SubDepartment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmployeeDetailRequest extends FormRequest
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
     */
    public function rules(Request $request): array
    {
        $table = (new EmploymentDetail())->getTable();

        // Get the current record ID (for edit)
        $employee_details = $request->route('employment_details');
        $id = 0; // default for create
        if ($employee_details instanceof EmploymentDetail) {
            $id = $employee_details->id;
        } elseif ($request->has('edit_id')) {
            $id = $request->input('edit_id');
        }

        // If user is logged in with a company, restrict company_id automatically
        $user_company_id = auth()->user()->company_id ?? null;

        $rules = [
            'company_id' => [
                'required',
                Rule::exists((new Company())->getTable(), 'id')
                    ->when($user_company_id, function ($query) use ($user_company_id) {
                        $query->where('id', $user_company_id);
                    }),
            ],
            'employee_id' => [
                'required',
                Rule::exists((new Employee())->getTable(), 'id'),
                Rule::unique($table, 'employee_id')->ignore($id)->whereNull('deleted_at'),
            ],
            'designation_id' => ['required', Rule::exists((new Designation())->getTable(), 'id')],
            'designation_type' => ['required', 'string', 'in:worker,employee'],
            'department_id' => ['required', Rule::exists((new Department())->getTable(), 'id')],
            'sub_department_id' => ['nullable', Rule::exists((new SubDepartment())->getTable(), 'id')],
            'process_id' => ['nullable', Rule::exists((new Process())->getTable(), 'id')],

            'employment_type' => ['required', Rule::exists((new EmployeeType())->getTable(), 'id')],
            'shift' => ['required', Rule::exists((new Shift())->getTable(), 'id')],
            'date_of_joining' => ['required', 'date'],
            'employment_confirmation_date' => ['required', 'date'],
            'employee_pf_no' => ['nullable  ', 'string'],
            'uan_no' => ['nullable', 'digits:12', 'numeric'],
            'payment_mode' => ['required', 'string'],
            'outdoor_attendance' => ['required'],
            // 'status' => ['required', 'in:active,inactive'],
        ];

        // dd("L-71", $rules, $request->all());

        return $rules;
    }
}
