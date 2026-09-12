<?php

namespace App\Http\Requests;

use App\Models\AssetsAllocationMaster;
use App\Models\Company;
use App\Models\Employee;
use App\Models\EmployeeEducationExperienceDetail;
use App\Models\EmployeeType;
use App\Models\Department;
use App\Models\Designation;
use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmployeeEducationExperienceRequest extends FormRequest
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
        // Try to get the ID from route model binding or from request input
        $employee_education_experience = $request->route('employee-education-experience');

        $id = 0; // default for create
        if ($employee_education_experience instanceof EmployeeEducationExperienceDetail) {
            $id = $employee_education_experience->id;
        } elseif ($request->has('edit_id')) {
            $id = $request->input('edit_id');
        }

        return [
            'company_id' => ['required', Rule::exists((new Company())->getTable(), 'id')],
            'employee_id' => ['required', Rule::exists((new Employee())->getTable(), 'id')],

            'degree' => ['required'],
            'institution_name' => ['required'],
            'month_of_passing_year' => ['required'],

            'company_name' => ['nullable'],
            'joining_date'=> 'nullable|date|before_or_equal:today',
            'left_date'=> 'nullable|date|after:joining_date|before_or_equal:today',
            'ctc_salary' => ['nullable'],

            'department_name' => ['nullable', Rule::exists((new Department())->getTable(), 'id')],
            'designation_name' => ['nullable', Rule::exists((new Designation())->getTable(), 'id')], 
            'effective_date' => ['nullable'],
            
            'document_type' => ['nullable'],
            'document_name' => ['nullable'],
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png,gif|max:10240',
            'status' => ['required', 'in:active,inactive'],
        ];
    }
}

