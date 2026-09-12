<?php

namespace App\Http\Requests;

use App\Models\Company;
use App\Models\Holiday;
use App\Models\MasterCountry;
use App\Models\MasterState;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HolidayRequest extends FormRequest
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
        $holidayId = $request->route('holiday') ?? 0;
        $companyId = $request->company_id;
        $fromDate = date('Y-m-d', strtotime($request->from_date));
        $toDate = date('Y-m-d', strtotime($request?->to_date));

        $rules = [
            'holiday_label' => 'required|string',
            'company_id' => [
                'required',
                Rule::exists((new Company())->getTable(), 'id')
            ],
            'country_id' => [
                'required',
                Rule::exists((new MasterCountry())->getTable(), 'id')
            ],
            'state_ids' => [
                'required',
                'array',
            ],
            'state_ids.*' => [
                'distinct', // ✅ ensures values in the array are unique
                Rule::exists((new MasterState())->getTable(), 'id'),
                function ($attribute, $value, $fail) use ($companyId, $fromDate, $toDate, $holidayId) {
                    // Check overlapping holiday for each state
                    $exists = DB::table((new Holiday())->getTable())
                        ->where('company_id', $companyId)
                        ->whereRaw("FIND_IN_SET(?, state_ids)", [$value])
                        ->where(function ($q) use ($fromDate, $toDate) {
                            $q->whereBetween('from_date', [$fromDate, $toDate])
                                ->orWhereBetween('to_date', [$fromDate, $toDate])
                                ->orWhere(function ($sub) use ($fromDate, $toDate) {
                                    $sub->where('from_date', '<=', $fromDate)
                                        ->where('to_date', '>=', $toDate);
                                });
                        })
                        ->when($holidayId, function ($q) use ($holidayId) {
                            $q->where('id', '!=', $holidayId);
                        })
                        ->exists();

                    if ($exists) {
                        $fail("A holiday already exists for the selected state in this date range.");
                    }
                }
            ],
            // 'date' => [
            //     'required',
            //     'date_format:d-m-Y',
            //     Rule::unique((new Holiday())->getTable())
            //         ->where(fn($query) => $query->where('company_id', $request->company_id))
            //         ->ignore($id, 'id'),
            // ],
            'from_date' => [
                'required',
                'date_format:d-m-Y',
            ],

            'to_date' => [
                'required',
                'date_format:d-m-Y',
                'after_or_equal:from_date', // ✅ ensures end date >= start date
            ],
            'remark' => 'nullable|string',
            'employee_designation_type' => 'required|in:both,employee,worker',
            'status' => 'required|in:active,inactive',
        ];

        // dd("L-60", $rules, $holidayId, $request->all(), $request->route('holiday'));
        return $rules;
    }
}
