<?php

namespace App\Http\Requests;

use App\Models\Company;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\ExpenseCategory;
use App\Models\ExpenseSubCategory;
use App\Models\Expense;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ExpenseRequest extends FormRequest
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
        $expense = $request->route('expense');
        $id = $expense instanceof Expense
            ? $expense->id
            : ($request->edit_id ?? 0);

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
            'team_person_id' => [
                'required',
                Rule::exists((new Employee())->getTable(), 'id')
                    ->where(function ($query) use ($request) {
                        return $query->where('company_id', $request->company_id);
                    }),
            ],
            'expense_category_id' => [
                'required',
                Rule::exists((new ExpenseCategory())->getTable(), 'id')
                    ->where(function ($query) use ($request) {
                        return $query->where('company_id', $request->company_id);
                    }),
            ],
            'expense_subcategory_id' => [
                'required',
                Rule::exists((new ExpenseSubCategory())->getTable(), 'id')
                    ->where(function ($query) use ($request) {
                        return $query->where('expense_category_id', $request->expense_category_id)
                            ->where('company_id', $request->company_id);
                    }),
            ],
            'date' => [
                'required',
                'date',
                'date_format:Y-m-d',
                function ($attribute, $value, $fail) {
                    $date = Carbon::parse($value);
                    $today = Carbon::today();
                    $past60Days = Carbon::today()->subDays(60);
                    
                    if ($date->isFuture()) {
                        $fail('Date cannot be in the future.');
                    }
                    
                    if ($date->lt($past60Days)) {
                        $fail('Date must be within the past 60 days or today.');
                    }
                }
            ],
            'req_amount' => ['required', 'numeric', 'min:0'],
            'remark' => 'nullable|string|max:1000',
            'image' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,pdf,doc,docx',
                'max:10240', // Max 10MB
            ],
        ];

        // Check if image is required based on expense subcategory
        if ($request->expense_subcategory_id) {
            $subcategory = ExpenseSubCategory::find($request->expense_subcategory_id);
            if ($subcategory && $subcategory->is_image_required == 1) {
                // For new records, image is required
                // For updates, check if subcategory changed or no existing attachment
                if ($id == 0) {
                    // New record
                    $rules['image'] = [
                        'required',
                        'file',
                        'mimes:jpg,jpeg,png,pdf,doc,docx',
                        'max:10240', // Max 10MB
                    ];
                } else {
                    // Update - check if subcategory changed
                    $existingExpense = Expense::find($id);
                    if ($existingExpense && $existingExpense->expense_subcategory_id != $request->expense_subcategory_id) {
                        // Subcategory changed, image required
                        $rules['image'] = [
                            'required',
                            'file',
                            'mimes:jpg,jpeg,png,pdf,doc,docx',
                            'max:10240',
                        ];
                    } elseif ($existingExpense && !$existingExpense->attachment) {
                        // No existing attachment, image required
                        $rules['image'] = [
                            'required',
                            'file',
                            'mimes:jpg,jpeg,png,pdf,doc,docx',
                            'max:10240',
                        ];
                    }
                }
            }
        }

        // Validate req_amount against subcategory limits if General type
        if ($request->expense_subcategory_id) {
            $subcategory = ExpenseSubCategory::find($request->expense_subcategory_id);
            if ($subcategory && $subcategory->expense_type === 'General') {
                $reqAmountRules = is_array($rules['req_amount']) ? $rules['req_amount'] : [$rules['req_amount']];
                if ($subcategory->min_amount !== null) {
                    $reqAmountRules[] = 'min:' . $subcategory->min_amount;
                }
                if ($subcategory->max_amount !== null) {
                    $reqAmountRules[] = 'max:' . $subcategory->max_amount;
                }
                $rules['req_amount'] = $reqAmountRules;
            }
        }

        // dd("L-153", $rules);
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
            'team_person_id.required' => 'Team person is required.',
            'team_person_id.exists' => 'Selected team person does not exist or does not belong to this company.',
            'expense_category_id.required' => 'Expense category is required.',
            'expense_category_id.exists' => 'Selected expense category does not exist or does not belong to this company.',
            'expense_subcategory_id.required' => 'Expense subcategory is required.',
            'expense_subcategory_id.exists' => 'Selected expense subcategory does not exist or does not belong to this category.',
            'date.required' => 'Date is required.',
            'date.date' => 'Date must be a valid date.',
            'date.date_format' => 'Date must be in Y-m-d format.',
            'date.before_or_equal' => 'Date cannot be in the future.',
            'date.past_60_days' => 'Date must be within the past 60 days or today.',
            'req_amount.required' => 'Request amount is required.',
            'req_amount.numeric' => 'Request amount must be a number.',
            'req_amount.min' => 'Request amount must be greater than or equal to 0.',
            'req_amount.max' => 'Request amount exceeds the maximum allowed amount for this subcategory.',
            'remark.max' => 'Remark cannot exceed 1000 characters.',
            'image.required' => 'Attachment is required for this expense subcategory.',
            'image.file' => 'Attachment must be a file.',
            'image.mimes' => 'Attachment must be a file of type: jpg, jpeg, png, pdf, doc, docx.',
            'image.max' => 'Attachment must not be greater than 10MB.',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Additional validation: Check if expense subcategory belongs to selected team person
            if ($this->expense_subcategory_id && $this->team_person_id) {
                $subcategory = ExpenseSubCategory::find($this->expense_subcategory_id);
                if ($subcategory && $subcategory->team_person_ids) {
                    $allowedPersonIds = explode(',', $subcategory->team_person_ids);
                    if (!in_array($this->team_person_id, $allowedPersonIds)) {
                        $validator->errors()->add('team_person_id', 'Selected team person is not allowed for this expense subcategory.');
                    }
                }
            }
        });
    }
}
