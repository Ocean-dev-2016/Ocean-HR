<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Company;
use App\Models\AccountHead;

class AccountHeadRequest extends FormRequest
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
    public function rules(): array
    {
        $id = $this->route('account_head'); // Match route parameter name (e.g. /account-head/{account_head})

        return [
            'company_id' => ['required', Rule::exists((new Company())->getTable(), 'id')],
            'date' => ['nullable', 'date'],
            'description' => ['nullable', 'string', 'max:500'],
            'debit_amount' => ['nullable', 'numeric', 'min:0'],
            'credit_amount' => ['nullable', 'numeric', 'min:0'],
            'balance' => ['nullable', 'numeric'],
            'to_date' => ['nullable', 'date'],
            'from_date' => ['nullable', 'date'],
            'status' => ['required', 'in:active,inactive'],
            'created_by' => ['nullable', 'string', 'max:255'],
            'updated_by' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'company_id.required' => 'Please select a company.',
            'company_id.exists' => 'The selected company is invalid.',

            'date.date' => 'Please enter a valid date.',
            'to_date.date' => 'Please enter a valid To Date.',
            'from_date.date' => 'Please enter a valid From Date.',

            'debit_amount.numeric' => 'Debit amount must be a valid number.',
            'credit_amount.numeric' => 'Credit amount must be a valid number.',
            'balance.numeric' => 'Balance must be a valid number.',

            'status.required' => 'Status is required.',
            'status.in' => 'Status must be either active or inactive.',
        ];
    }
}
