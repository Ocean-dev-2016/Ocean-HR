<?php

namespace App\Http\Requests;

use App\Models\Company;
use App\Models\Branch;
use App\Models\Shift;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;


class ShiftRequest extends FormRequest
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
        $id = $request->route('shifts') ?? 0;
        $id = (isset($request->edit_id) && $id == 0) ? $request->edit_id : 0;
        //  dd($id, $request->all());
        
        $rules = [
            'company_id' => [
                'required',
                Rule::exists((new Company())->getTable(), 'id')
            ],
            'branch_id' => [
                'nullable',
                Rule::exists((new Branch())->getTable(), 'id')
            ],

            // 'name' => 'required|string|max:255|unique:' . (new MasterCity())->getTable() . ',name',
            'name' => [
                'required',
                Rule::unique((new Shift())->getTable())->where(function ($query) use ($request) {
                    $query->where('company_id', $request->company_id);
                    if ($request->branch_id) {
                        $query->where('branch_id', $request->branch_id);
                    }
                    return $query;
                })->ignore($id),
            ],

            'punch_in_minimum' => [
                'required',
                'date_format:H:i:s',
                // 'regex:/^(?:[01]\d|2[0-3]):[0-5]\d:(?:[0-5]\d)$/'
                function ($attribute, $value, $fail) {
                    if (strtotime($value) < strtotime('00:00:00') || strtotime($value) > strtotime('23:59:00')) {
                        $fail('The ' . $attribute . ' must be between 00:00:00 and 23:59:00.');
                    }
                },
            ],
            'punch_out' => [
                'required',
                'date_format:H:i:s',
                // 'regex:/^(?:[01]\d|2[0-3]):[0-5]\d:(?:[0-5]\d)$/'
                function ($attribute, $value, $fail) {
                    if (strtotime($value) < strtotime('00:00:00') || strtotime($value) > strtotime('23:59:00')) {
                        $fail('The ' . $attribute . ' must be between 00:00:00 and 23:59:00.');
                    }
                },
            ],
            'auto_punch_out' => [
                'required',
                'date_format:H:i:s',
                // 'regex:/^(?:[01]\d|2[0-3]):[0-5]\d:(?:[0-5]\d)$/'
                function ($attribute, $value, $fail) {
                    if (strtotime($value) < strtotime('00:00:00') || strtotime($value) > strtotime('23:59:00')) {
                        $fail('The ' . $attribute . ' must be between 00:00:00 and 23:59:00.');
                    }
                },
            ],
            'in_out_grace_period' => ['nullable', 'numeric', 'min:0'],
            'grace_period' => ['nullable', 'numeric', 'min:0'],
            'employee_max_working_hours' => ['nullable', 'numeric', 'min:0', 'max:23'],

            'working_hour' => ['required'],
            'breaking_hour' => [
                'required',
                function ($attribute, $value, $fail) use ($request) {
                    $workingHour = $request->working_hour;
                    if ($workingHour && strtotime($value) >= strtotime($workingHour)) {
                        $fail('The ' . str_replace('_', ' ', $attribute) . ' must be less than working hour.');
                    }
                }
            ],
            'half_day_hour' => [
                'required',
                function ($attribute, $value, $fail) use ($request) {
                    $workingHour = $request->working_hour;
                    if ($workingHour && strtotime($value) >= strtotime($workingHour)) {
                        $fail('The ' . str_replace('_', ' ', $attribute) . ' must be less than working hour.');
                    }
                }
            ],
            'present_day_hour' => [
                'required',
                function ($attribute, $value, $fail) use ($request) {
                    $workingHour = $request->working_hour;
                    if ($workingHour && strtotime($value) >= strtotime($workingHour)) {
                        $fail('The ' . str_replace('_', ' ', $attribute) . ' must be less than working hour.');
                    }
                }
            ],

            'status' => 'required|in:active,inactive',
        ];
        // dd("L-33", $rules, $id, $request->all(), $request->route('master-city'), $request->route()->parameters());
        return $rules;
    }
}
