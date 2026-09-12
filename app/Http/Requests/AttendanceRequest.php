<?php

namespace App\Http\Requests;

use App\Models\Attendance;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;

class AttendanceRequest extends FormRequest
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
        $rules = [
            'company_id' => ['required', Rule::exists((new Company())->getTable(), 'id')],
            'employee_id' => ['required', Rule::exists((new Employee())->getTable(), 'id')],
            'shift_id'    => ['required', Rule::exists((new Shift())->getTable(), 'id')],
            // 'punch_in_time'  => ['nullable', 'date_format:H:i:s'],
            'punch_in_time' => [
                'nullable',
                'date_format:H:i:s', // Keep strict or change to regex? Laravel doesn't support array for date_format OR logic easily in string.
                                     // Better to use regex or just 'date_format:H:i:s' AND manually parse in prepareForValidation?
                                     // Actually, passing multiple formats to date_format isn't standard in all versions.
                                     // Let's use regex or just basic string and parse in closure.
                                     // Or just 'date_format:H:i:s' for now and fix input?
                                     // Wait, 'date_format:H:i' vs 'H:i:s'.
                                     // I will remove 'date_format' and use custom closure to validate flexible time.
                function ($attribute, $value, $fail) use ($request) {
                    try {
                        if ($value) {
                            // Validate format H:i or H:i:s
                            if (!preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/', $value)) {
                                $fail('The punch in time must match the format H:i or H:i:s.');
                                return;
                            }

                            $now = Carbon::now();

                            if ($request->attendance_date) {
                                $attendanceDate = Carbon::parse($request->attendance_date)->startOfDay();
                                $punchIn = Carbon::parse($request->attendance_date . ' ' . $value);

                                if ($attendanceDate->isToday() && $punchIn->gt($now)) {
                                    $fail('Punch In time should not be greater than the current date and time.');
                                }
                            }
                        }
                    } catch (\Exception $e) {
                         // ignore parse errors here, let regex handle format checks
                    }
                },
            ],

            'attendance_date' => [
                'required',
                'date',
            ],
            // 'create_date' => [
            //     'required',
            //     'date',
            // ],

            'attendace_type' => 'required',
            'remark' => 'nullable',
            'status' => 'nullable|in:active,inactive',
            'txn_id' => 'nullable|string|max:255',
            'records_source' => 'nullable|in:api,manually,others',
            'requested_data' => 'nullable|string',
        ];

        return $rules;
    }
}
