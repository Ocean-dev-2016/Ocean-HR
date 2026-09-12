<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Loan;
use App\Models\LoanTypes;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;

class LoanRequest extends FormRequest
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

        $id = $request->route('loan') ?? 0;
        //  dd($id);
        $rules = [
            'company_id' => [
                'required',
                Rule::exists((new Company())->getTable(), 'id')
            ],
            'employee_id' => [
                'required',
                Rule::exists((new Employee())->getTable(), 'id')->where(function ($query) use ($request) {
                    return $query->where('company_id', $request->company_id);
                }),
            ],
            'loan_type_id' => [
                'required',
                Rule::exists((new LoanTypes())->getTable(), 'id')->where(function ($query) use ($request) {
                    return $query->where('company_id', $request->company_id);
                }),
            ],
            'loan_amount' => 'required|numeric|min:1',
            'total_installments' => 'required|integer|min:1',

            'interest_rate'      => 'nullable|numeric|min:0',   // ✅ can be null or 0
            'interest_type'      => 'nullable|in:flat,reducing', // ✅ defaults to flat

            'loan_date' => 'required|date',
            'remark' => 'nullable|string',
            'status' => 'required|in:'.implode(",", array_keys(Loan::$loanStatus)),
        ];
        // dd("L-44", $rules, $id, $request->all(), $request->route('leave_type'), $request->route()->parameters(), implode(",", array_keys(Loan::$loanStatus)));
        return $rules;
    }
}
