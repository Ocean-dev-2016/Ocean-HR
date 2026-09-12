<?php

namespace App\Http\Requests;

use App\Models\Company;
use App\Models\Branch;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;


class BranchRequest extends FormRequest
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
        $id = $request->route('branch') ?? 0;
        //  dd($id);
        $rules = [
            'company_id' => [
                'required',
                Rule::exists((new Company())->getTable(), 'id')
            ],

            // Branch name must be unique per company
            'name' => [
                'required',
                Rule::unique((new Branch())->getTable())->where(function ($query) use ($request) {
                    return $query->where('company_id', $request->company_id);
                })->ignore($id),
            ],

            // Optional prefix (string)
            'prefix' => ['nullable', 'string', 'max:255'],

            // Employee code start: numeric (allows "00001"), min 1, cast to int when saving
            'employee_code_start' => ['nullable', 'numeric', 'min:1'],

            // Canteen time fields are optional but must be valid HH:MM:SS when provided
            'canteen_max_time' => ['nullable', 'date_format:H:i:s'],
            'canteen_min_time' => ['nullable', 'date_format:H:i:s'],

            // Optional address
            'branch_address' => ['nullable', 'string'],

            'status' => 'required|in:active,inactive',
        ];
        // dd("L-33", $rules, $id, $request->all(), $request->route('master-city'), $request->route()->parameters());
        return $rules;
    }
}
