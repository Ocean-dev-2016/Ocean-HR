<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OperationsRateListRequest extends FormRequest
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
        $rules = [];

        // Common required fields
        $rules['operation'] = 'required|string';
        $rules['month'] = 'required|integer|min:1|max:12';
        $rules['year'] = 'required|integer|min:2000|max:2100';

        $operation = $this->input('operation');

        // Define operation groups (those that use a global employee selector at the top)
        $globalEmployeeOps = ['Lathe Employee wise', 'CLEANING', 'GRINDING', 'CNC', 'BUFF', 'COATING', 'ASS-1', 'ASS-2', 'RRL', 'BUTTERFLY', 'FLEXIBLE', 'CORE', 'FOUNDRY'];

        if ($this->has('items')) {
            $rules['company_id'] = 'required|exists:companies,id';
            $rules['items'] = 'required|array|min:1';

            // Default per-item rules
            $rules['items.*.product_id'] = 'nullable|exists:products,id';
            $rules['items.*.grade_id'] = 'nullable|exists:grades,id';
            $rules['items.*.employee_id'] = 'nullable|exists:employees,id';
            $rules['items.*.contract_process_id'] = 'nullable|exists:contract_processes,id';
            $rules['items.*.rate'] = 'nullable|numeric|min:0';

            // Make day columns numeric (may be empty)
            for ($i = 1; $i <= 31; $i++) {
                $rules['items.*.day_' . $i] = 'nullable|numeric';
                $rules['items.*.day_' . $i . '_r'] = 'nullable|numeric';
                $rules['items.*.day_' . $i . '_ot'] = 'nullable|numeric';
            }

            // Conditional requirements depending on operation
            if (in_array($operation, $globalEmployeeOps)) {
                $rules['global_employee_id'] = 'required|exists:employees,id';
                
                if ($operation === 'Lathe Employee wise') {
                    $rules['items.*.product_id'] = [
                        'required',
                        Rule::exists('products', 'id')->where(function ($query) {
                            $query->where('operation', 'Lathe Employee wise')->whereNotNull('process');
                        }),
                    ];
                } else {
                    $rules['items.*.product_id'] = 'required|exists:products,id';
                    $rules['items.*.grade_id'] = 'nullable|exists:grades,id';
                }
            } else {
                // Default: product/grade per item
                $rules['items.*.product_id'] = 'required|exists:products,id';
                $rules['items.*.grade_id'] = 'nullable|exists:grades,id';
            }
        } else {
            // No items: require identifying fields so single record can be created
            $rules['company_id'] = 'required|exists:companies,id';
            $rules['product_id'] = 'required|exists:products,id';
            $rules['grade_id'] = 'nullable|exists:grades,id';
            $rules['employee_id'] = 'nullable|exists:employees,id';
            $rules['contract_process_id'] = 'nullable|exists:contract_processes,id';
            $rules['status'] = 'nullable|in:active,inactive';
            $rules['rate'] = 'nullable|numeric|min:0';

            // For certain operations require global employee
            if (in_array($operation, $globalEmployeeOps)) {
                $rules['global_employee_id'] = 'required|exists:employees,id';
            }
        }
        return $rules;
    }

    public function attributes(): array
    {
        $attributes = [];
        $operation = $this->input('operation');

        if ($this->has('items')) {
            foreach ($this->input('items', []) as $key => $value) {
                if ($operation === 'Lathe Employee wise') {
                    $attributes["items.{$key}.product_id"] = 'Process Name';
                } else {
                    $attributes["items.{$key}.product_id"] = 'Product Name';
                    $attributes["items.{$key}.grade_id"] = 'Grade Name';
                }
            }
        }

        return $attributes;
    }
}
