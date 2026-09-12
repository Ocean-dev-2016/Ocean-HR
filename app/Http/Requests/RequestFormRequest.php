<?php

namespace App\Http\Requests;

use App\Models\Company;
use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Validation\Rule;
use Illuminate\Http\Request;

class RequestFormRequest extends FormRequest
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
        $id = $request->route('request') ?? 0;

        $rules = [
            'company_id' => [
                'required',
                Rule::exists((new Company())->getTable(), 'id')
            ],
            'request_from_employee_name' => [
                'required',
                'string',
                'max:255',
                // must exist in employees table for the given company
                Rule::exists((new Employee())->getTable(), 'id')
                    ->where(fn($q) => $q->whereColumn('company_id', 'company_id')),
            ],
            'request_to_employee_name'   => [
                'required',
                'string',
                'max:255',
                // must exist in employees table for the given company
                Rule::exists((new Employee())->getTable(), 'id')
                    ->where(fn($q) => $q->whereColumn('company_id', 'company_id')),
                // must not be the same as "from"
                'different:request_from_employee_name',
            ],
            'attechment'                 => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx'],
            'request_description'        => ['required', 'string', 'max:1000'],
            'status'                     => ['required', 'in:active,inactive'],
        ];

        // dd("L-42", $request?->all());

        return $rules;
    }


    public function messages()
    {
        return [
            'request_to_employee_name.different' => 'The request to employee field and request from employee must be different.',
        ];
    }
}
