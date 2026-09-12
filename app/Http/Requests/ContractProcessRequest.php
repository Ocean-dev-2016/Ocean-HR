<?php

namespace App\Http\Requests;

use App\Models\Company;
use App\Models\ContractProcess;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;


class ContractProcessRequest extends FormRequest
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
        $id = $request->route('contract_process') ?? 0;
        
        $rules = [
            'company_id' => [
                'required',
                Rule::exists((new Company())->getTable(), 'id')
            ],

            'name' => [
                'required',
                Rule::unique((new ContractProcess())->getTable())->where(function ($query) use ($request) {
                    return $query->where('company_id', $request->company_id);
                })->ignore($id),
            ],

            'rate' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive',
        ];
        
        return $rules;
    }
}
