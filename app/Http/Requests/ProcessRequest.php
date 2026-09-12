<?php

namespace App\Http\Requests;

use App\Models\Company;
use App\Models\Department;
use App\Models\Process;
use App\Models\SubDepartment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;

class ProcessRequest extends FormRequest
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
        $process = $request->route('process') ?? 0;
        $id = $process instanceof Process
            ? $process->id
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
            'sub_department_id' => [
                'required',
                Rule::exists((new SubDepartment())->getTable(), 'id')
            ],
            'name' => [
                'required',
                Rule::unique('processes', 'name')
                    ->where(function ($query) use ($request) {
                        return $query->where('company_id', $request->company_id)
                            ->where('department_id', $request->department_id)
                            ->where('sub_department_id', $request->sub_department_id); // include sub_department_id
                    })
                    ->ignore($id),
            ],
            'status' => 'required|in:active,inactive',
        ];
        // dd("L-33", $rules, $id, $request->all(), $request->route('master-city'), $request->route()->parameters());
        return $rules;
    }
}
