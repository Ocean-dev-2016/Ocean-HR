<?php

namespace App\Http\Requests;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerType;
use App\Models\Department;
use App\Models\Employee;
use App\Models\PaymentReceipt;
use App\Models\TeamPerson;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;

class PaymentReceiptRequest extends FormRequest
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
        $id = $request->route('payment_receipt') ?? 0;

        return [
            'company_id' => [
                'required',
                Rule::exists((new Company())->getTable(), 'id'),
            ],
            'branch_id' => [
                'nullable',
                Rule::exists((new Branch())->getTable(), 'id'),
            ],
            'department' => [
                'required',
                Rule::exists((new Department())->getTable(), 'id'),
            ],
            'employee_id' => [
                'required',
                Rule::exists((new Employee())->getTable(), 'id'),
            ],
            'effect_on_month' => [
                'required',

            ],
            'effect_of_year' => [
                'required',

            ],
            'date' => [
                'required',

            ],
            'amount' => [
                'required',
                'numeric',

            ],
            'account_head_id' => [
                'required',

            ],
            'payment_type' => [
                'required',
            ],
            'payment_mode' => [
                'required',
            ],
            'upi_no' => [
                'required_if:payment_type,upi',
                'nullable',
            ],
            'cheque_no' => [
                'required_if:payment_type,cheque',
                'nullable',
            ],
            'receipt_no' => [
                'required',

            ],
            'remark' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }
}
