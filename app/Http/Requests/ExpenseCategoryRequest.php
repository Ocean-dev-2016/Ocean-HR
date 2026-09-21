<?php

namespace App\Http\Requests;

use App\Models\Company;
use App\Models\Branch;
use App\Models\ExpenseCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;

class ExpenseCategoryRequest extends FormRequest
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
        $expenseCategory = $request->route('expense_category') ?? $request->route('expense-category');
        $id = $expenseCategory instanceof ExpenseCategory
            ? $expenseCategory->id
            : ($expenseCategory ?: ($request->edit_id ?? $request->id ?? 0));

        $rules = [
            'company_id' => [
                'required',
                Rule::exists((new Company())->getTable(), 'id')
            ],
            'branch_id' => [
                'nullable',
                Rule::exists((new Branch())->getTable(), 'id')
                    ->where(function ($query) use ($request) {
                        return $query->where('company_id', $request->company_id);
                    }),
            ],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique((new ExpenseCategory())->getTable(), 'name')
                    ->where(function ($query) use ($request) {
                        $query->where('company_id', $request->company_id);
                        if (!empty($request->branch_id)) {
                            $query->where('branch_id', $request->branch_id);
                        } else {
                            $query->where(function ($q) {
                                $q->whereNull('branch_id')->orWhere('branch_id', 0)->orWhere('branch_id', '');
                            });
                        }
                    })
                    ->ignore($id),
            ],
            'status' => 'required|in:active,inactive',
        ];

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'company_id.required' => 'Company is required.',
            'company_id.exists' => 'Selected company does not exist.',
            'branch_id.exists' => 'Selected branch does not exist or does not belong to this company.',
            'name.required' => 'Expense category name is required.',
            'name.unique' => 'This expense category name already exists for this company and branch.',
            'status.required' => 'Status is required.',
            'status.in' => 'Status must be either active or inactive.',
        ];
    }
}
