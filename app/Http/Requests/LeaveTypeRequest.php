<?php

namespace App\Http\Requests;

use App\Models\Company;
use App\Models\LeaveType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;


class LeaveTypeRequest extends FormRequest
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
        $id = $request->route('leave_type') ?? 0;
        //  dd($id);
        $rules = [
            'company_id' => [
                'required',
                Rule::exists((new Company())->getTable(), 'id')
            ],

            // 'name' => 'required|string|max:255|unique:' . (new MasterCity())->getTable() . ',name',
            'sort_name' => [
                'required',
                Rule::unique((new LeaveType())->getTable())->where(function ($query) use ($request) {
                    return $query->where('company_id', $request->company_id);
                })->ignore($id),
            ],
            'full_name' => 'required',
            'mode' => 'required',
            'carry_forward' => 'nullable',
            'attachment_required' => 'nullable',
            'attachment_required_days' => 'nullable|integer|min:0',
            'count' => 'nullable|numeric|max:365|min:0',
            'status' => 'required|in:active,inactive',
        ];
        // dd("L-47", $rules, $id, $request->all(), $request->route('leave_type'), $request->route()->parameters());
        return $rules;
    }
}
