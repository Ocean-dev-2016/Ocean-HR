<?php

namespace App\Http\Requests;

use App\Helpers\Helper;
use App\Models\Company;
use App\Models\Branch;
use App\Models\ExpenseCategory;
use App\Models\ExpenseSubCategory;
use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExpenseSubCategoryRequest extends FormRequest
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
        $expenseSubCategory = $this->route('expense-subcategory');
        $id = $expenseSubCategory instanceof ExpenseSubCategory
            ? $expenseSubCategory->id
            : ($this->input('edit_id') ?? 0);

        $branchRequiredNullable = 'nullable';
        $companyId = $this->company_id;
        if ($companyId) {
            // Assuming getCompanyBranchType is a helper that returns 'multi' or 'single'
            $branchType = Helper::getCompanyBranchType($companyId);
            if ($branchType === 'multiple' && empty($value)) {
                $branchRequiredNullable = 'required';
            }
        }
        $rules = [
            'company_id' => [
                'required',
                Rule::exists((new Company())->getTable(), 'id')
            ],
            'branch_id' => [
                $branchRequiredNullable,
                // branch_id is required if the company has multiple branches
                Rule::exists((new Branch())->getTable(), 'id')
                    ->where(function ($query) {
                        return $query->where('company_id', $this->company_id);
                    }),
            ],
            'expense_category_id' => [
                'required',
                Rule::exists((new ExpenseCategory())->getTable(), 'id')
                    ->where(function ($query) {
                        $query->where('company_id', $this->company_id);
                        if ($this->branch_id) {
                            $query->where('branch_id', $this->branch_id);
                        }
                    }),
            ],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique((new ExpenseSubCategory())->getTable(), 'name')
                    ->where(function ($query) {
                        $query->where('company_id', $this->company_id)
                            ->where('expense_category_id', $this->expense_category_id);
                        if ($this->branch_id) {
                            $query->where('branch_id', $this->branch_id);
                        }
                        // Ensure uniqueness for each selected team person
                        if (is_array($this->team_person_ids) && count($this->team_person_ids)) {
                            // Check if any of the team_person_ids exist in the comma separated team_person_ids column
                            $query->where(function ($q) {
                                foreach ($this->team_person_ids as $personId) {
                                    $q->orWhereRaw("FIND_IN_SET(?, team_person_ids)", [$personId]);
                                }
                            });
                        }
                    })
                    ->ignore($id),
            ],
            'expense_type' => 'required|in:General,KM,Food',
            'team_person_ids' => [
                'required',
                'array',
                'min:1'
            ],
            'team_person_ids.*' => [
                'required',
                Rule::exists((new Employee())->getTable(), 'id')
                    ->where(function ($query) {
                        return $query->where('company_id', $this->company_id);
                    }),
            ],
            'is_image_required' => 'nullable|boolean',
            'status' => 'required|in:active,inactive',
        ];

        // Conditional validation based on expense_type
        $expenseType = $this->expense_type ?? $this->input('expense_type');

        if ($expenseType === 'General') {
            $rules['min_amount'] = 'required|numeric|min:0';
            $rules['max_amount'] = [
                'required',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) {
                    $minAmount = $this->input('min_amount');
                    if ($minAmount && $value < $minAmount) {
                        $fail('Maximum amount must be greater than or equal to minimum amount.');
                    }
                }
            ];
            $rules['per_km_rate'] = 'nullable|numeric|min:0';
            $rules['fix_amount'] = 'nullable|numeric|min:0';
            $rules['from_time'] = 'nullable|date_format:H:i';
            $rules['to_time'] = 'nullable|date_format:H:i';
        } elseif ($expenseType === 'KM') {
            $rules['per_km_rate'] = 'required|numeric|min:0';
            $rules['min_amount'] = 'nullable|numeric|min:0';
            $rules['max_amount'] = 'nullable|numeric|min:0';
            $rules['fix_amount'] = 'nullable|numeric|min:0';
            $rules['from_time'] = 'nullable|date_format:H:i';
            $rules['to_time'] = 'nullable|date_format:H:i';
        } elseif ($expenseType === 'Food') {
            $rules['fix_amount'] = 'required|numeric|min:0';
            $rules['from_time'] = 'required|date_format:H:i';
            $rules['to_time'] = [
                'required',
                'date_format:H:i',
                function ($attribute, $value, $fail) {
                    $fromTime = $this->input('from_time');
                    if ($fromTime && $value && strtotime($value) <= strtotime($fromTime)) {
                        $fail('To time must be after from time.');
                    }
                }
            ];
            $rules['min_amount'] = 'nullable|numeric|min:0';
            $rules['max_amount'] = 'nullable|numeric|min:0';
            $rules['per_km_rate'] = 'nullable|numeric|min:0';
        } else {
            // Default: all fields nullable if expense_type not set yet
            $rules['min_amount'] = 'nullable|numeric|min:0';
            $rules['max_amount'] = 'nullable|numeric|min:0';
            $rules['per_km_rate'] = 'nullable|numeric|min:0';
            $rules['fix_amount'] = 'nullable|numeric|min:0';
            $rules['from_time'] = 'nullable|date_format:H:i';
            $rules['to_time'] = 'nullable|date_format:H:i';
        }

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
            'expense_category_id.required' => 'Expense category is required.',
            'expense_category_id.exists' => 'Selected expense category does not exist or does not belong to this company and branch.',
            'name.required' => 'Expense subcategory name is required.',
            'name.unique' => 'This expense subcategory name already exists for this category and branch.',
            'expense_type.required' => 'Expense type is required.',
            'expense_type.in' => 'Expense type must be General, KM, or Food.',
            'team_person_ids.required' => 'At least one team person is required.',
            'team_person_ids.array' => 'Team persons must be an array.',
            'team_person_ids.min' => 'At least one team person must be selected.',
            'team_person_ids.*.exists' => 'One or more selected team persons do not exist.',
            'min_amount.required' => 'Minimum amount is required for General type.',
            'min_amount.numeric' => 'Minimum amount must be a number.',
            'min_amount.min' => 'Minimum amount must be greater than or equal to 0.',
            'max_amount.required' => 'Maximum amount is required for General type.',
            'max_amount.numeric' => 'Maximum amount must be a number.',
            'max_amount.min' => 'Maximum amount must be greater than or equal to 0.',
            'max_amount.gte' => 'Maximum amount must be greater than or equal to minimum amount.',
            'per_km_rate.required' => 'Per KM rate is required for KM type.',
            'per_km_rate.numeric' => 'Per KM rate must be a number.',
            'per_km_rate.min' => 'Per KM rate must be greater than or equal to 0.',
            'fix_amount.required' => 'Fix amount is required for Food type.',
            'fix_amount.numeric' => 'Fix amount must be a number.',
            'fix_amount.min' => 'Fix amount must be greater than or equal to 0.',
            'from_time.required' => 'From time is required for Food type.',
            'from_time.date_format' => 'From time must be in valid time format (HH:MM).',
            'to_time.required' => 'To time is required for Food type.',
            'to_time.date_format' => 'To time must be in valid time format (HH:MM).',
            'to_time.after' => 'To time must be after from time.',
            'status.required' => 'Status is required.',
            'status.in' => 'Status must be either active or inactive.',
        ];
    }
}
