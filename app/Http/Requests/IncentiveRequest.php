<?php

namespace App\Http\Requests;

use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Incentive;
use App\Models\SubDepartment;
use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Validation\Rule;
use Illuminate\Http\Request;

class IncentiveRequest extends FormRequest
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
        $incentive = $request->route('incentive') ?? 0;
        $id = $incentive instanceof Incentive
            ? $incentive->id
            : ($request->edit_id ?? 0);
        $rules = [
            'company_id' => [
                'required',
                Rule::exists((new Company())->getTable(), 'id')
            ],
            'department_id' => [
                'required',
                Rule::exists((new Department())->getTable(), 'id')
            ],
            'employee_id' => [
                'required',
                Rule::exists((new Employee())->getTable(), 'id')
            ],
            'incentive_value' => [
                'required',
                'numeric',
                'min:0',
                'max:100',
            ],

            'incentive_amount' => [
                'required',
            ],
            'status' => 'required|in:active,inactive',
        ];
        return $rules;
    }
}
