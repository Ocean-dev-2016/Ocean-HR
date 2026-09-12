<?php

namespace App\Http\Requests;

use App\Models\Company;
use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;


class ProductRequest extends FormRequest
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
        $id = $request->route('product') ?? 0;

        $rules = [
            'company_id' => [
                'required',
                Rule::exists((new Company())->getTable(), 'id')
            ],

            'name' => [
                'required',
                Rule::unique((new Product())->getTable())->where(function ($query) use ($request) {
                    $process = $request->process ?: null;
                    return $query->where('company_id', $request->company_id)
                        ->where('operation', $request->operation)
                        ->where('process', $process)
                        ->whereNull('deleted_at');
                })->ignore($id),
            ],

            'status' => 'required|in:active,inactive',
            'rate' => 'nullable|numeric|min:0',
            'operation' => 'required|string|max:255',
            'process' => 'nullable|string|max:255',
            'unit' => 'nullable|string|in:KG,PCS',
            'ot_text' => 'nullable|string|max:255',
            'rejection_rate' => 'nullable|numeric|min:0',
        ];

        return $rules;
    }
}
