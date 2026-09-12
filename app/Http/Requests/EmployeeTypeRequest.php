<?php

namespace App\Http\Requests;

use App\Models\Company;
use App\Models\EmployeeType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;


class EmployeeTypeRequest extends FormRequest
{

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
        $id = $this->route('employee_type') ?? 0; // 👈 use the correct param key

        return [
            'company_id' => [
                'required',
                Rule::exists((new Company())->getTable(), 'id'),
            ],
            'name' => [
                'required',
                Rule::unique((new EmployeeType())->getTable())
                    ->where(fn($query) => $query->where('company_id', $request->company_id))
                    ->ignore($id, 'id'), // 👈 this will now match correctly
            ],
            'status' => 'required|in:active,inactive',
        ];
    }


}
