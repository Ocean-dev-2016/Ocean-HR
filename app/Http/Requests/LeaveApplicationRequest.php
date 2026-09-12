<?php

namespace App\Http\Requests;

use App\Helpers\Helper;
use App\Models\Branch;
use App\Models\Company;
use App\Models\LeaveApplication;
use App\Models\LeaveType;
use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;

class LeaveApplicationRequest extends FormRequest
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
        $id = $request->route('leave_application') ?? 0; // Adjust 'leave' based on your route parameter name
        $rules = [
            'company_id' => [
                'required',
                Rule::exists((new Company())->getTable(), 'id'),
            ],
            'branch_id' => [
                'nullable',
                Rule::exists((new Branch())->getTable(), 'id'),
            ],
            'employee_id' => [
                'required',
                Rule::exists((new Employee())->getTable(), 'id'),
            ],
            'leave_type_id' => [
                'required',
                Rule::exists((new LeaveType())->getTable(), 'id')
            ],
            'halfday_fullday' => [
                'required',
                'in:' . implode(",", array_keys(LeaveApplication::$leaveForDay))
            ],
            // 'singleday_multipleday' => [
            //     'nullable',
            //     'required_if:halfday_fullday,fullday',
            //     'in:' . implode(",", array_keys(LeaveApplication::$leaveByDays)),
            // ],
            'firsthalf_secondhalf' => [
                'required_if:halfday_fullday,halfday',
                'nullable',
                'in:' . implode(",", array_keys(LeaveApplication::$leaveForHalfdays)),
            ],
            'fromdate_time' => [
                'required',
            ],
            'todate_time' => [
                'required_if:halfday_fullday,fullday',
                'nullable',
            ],
            'leave_reason' => [
                'required',
                'string',
                'max:1000',
                // Conditionally require the rejection_reason if status is 'reject'
                // Rule::requiredIf(function () use ($request) {
                //     return $request->input('status') === 'reject';
                // })
            ],
            'status' => [
                'required',
                'in:pending,pass,reject'
            ],
            'attachment' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,pdf,doc,docx',
                // 'max:10MB',
            ],
        ];
        return $rules;
    }

    public function messages(): array
    {
        return [
            'leave_type_id.required' => 'The leave type field is required.',
        ];
    }


    public function withValidator($validator)
    {

        $validator->after(function ($validator) {
            $halfdayFullday = $this->input('halfday_fullday');
            $fromDateTime = $this->input('fromdate_time');
            $toDateTime = $this->input('todate_time');

            // Validate date format based on day type
            if ($halfdayFullday === 'fullday') {
                // For fullday, expect date-only format (d-m-Y)
                if ($fromDateTime && !preg_match('/^\d{2}-\d{2}-\d{4}$/', $fromDateTime)) {
                    $validator->errors()->add('fromdate_time', 'The from date must be in DD-MM-YYYY format for full day leave.');
                }
                if ($toDateTime && !preg_match('/^\d{2}-\d{2}-\d{4}$/', $toDateTime)) {
                    $validator->errors()->add('todate_time', 'The to date must be in DD-MM-YYYY format for full day leave.');
                }
            } else if ($halfdayFullday === 'halfday') {
                // For halfday, expect datetime format (d-m-Y H:i)
                if ($fromDateTime && !preg_match('/^\d{2}-\d{2}-\d{4} \d{2}:\d{2}$/', $fromDateTime)) {
                    $validator->errors()->add('fromdate_time', 'The from date must be in DD-MM-YYYY HH:MM format for half day leave.');
                }
            }

            // Validate that todate_time is after fromdate_time
            if ($fromDateTime && $toDateTime && $halfdayFullday === 'fullday') {
                try {
                    $fromDate = \Carbon\Carbon::createFromFormat('d-m-Y', $fromDateTime)->startOfDay();
                    $toDate = \Carbon\Carbon::createFromFormat('d-m-Y', $toDateTime)->startOfDay();
                    if ($toDate->lt($fromDate)) {
                        $validator->errors()->add('todate_time', 'The to date must be after or equal to the from date.');
                    }
                } catch (\Exception $e) {
                    // Date parsing failed, skip this validation
                }
            } else if ($fromDateTime && $toDateTime && $halfdayFullday === 'halfday') {
                try {
                    $fromDate = \Carbon\Carbon::createFromFormat('d-m-Y H:i', $fromDateTime);
                    $toDate = \Carbon\Carbon::createFromFormat('d-m-Y H:i', $toDateTime);
                    if ($toDate->lt($fromDate)) {
                        $validator->errors()->add('todate_time', 'The to date must be after or equal to the from date.');
                    }
                } catch (\Exception $e) {
                    // Date parsing failed, skip this validation
                }
            }

            $edit_id = $this->route('leave_application') ?? 0;
            $employee_id = $this->input('employee_id');

            $firstOrSecond = $this->input('firsthalf_secondhalf');
            $halfFullDay = $this->input('halfday_fullday');
            $leave_type_id = $this->input('leave_type_id');

            // 1. Overlapping Leave Check (Fixing existing logic)
            if ($employee_id && $fromDateTime) {
                $existingLeave = LeaveApplication::query()
                    ->where('status', '!=', 'rejected')
                    ->where('employee_id', $employee_id);

                if ($edit_id) {
                    $existingLeave = $existingLeave->where('id', '!=', $edit_id);
                }

                if ($halfFullDay === 'halfday') {
                    $existingLeave = $existingLeave->where('halfday_fullday', 'halfday')
                        ->where('firsthalf_secondhalf', $firstOrSecond)
                        ->whereDate('fromdate_time', \Carbon\Carbon::parse($fromDateTime)->toDateString());
                } else {
                    $fromDateStr = \Carbon\Carbon::parse($fromDateTime)->toDateString();
                    $toDateStr = $toDateTime ? \Carbon\Carbon::parse($toDateTime)->toDateString() : $fromDateStr;

                    $existingLeave = $existingLeave->where(function ($query) use ($fromDateStr, $toDateStr) {
                        $query->where(function ($q) use ($fromDateStr, $toDateStr) {
                            $q->whereDate('fromdate_time', '<=', $fromDateStr)
                                ->whereDate('todate_time', '>=', $fromDateStr);
                        })->orWhere(function ($q) use ($fromDateStr, $toDateStr) {
                            $q->whereDate('fromdate_time', '<=', $toDateStr)
                                ->whereDate('todate_time', '>=', $toDateStr);
                        })->orWhere(function ($q) use ($fromDateStr, $toDateStr) {
                            $q->whereDate('fromdate_time', '>=', $fromDateStr)
                                ->whereDate('todate_time', '<=', $toDateStr);
                        });
                    });
                }

                if ($existingLeave->exists()) {
                    $validator->errors()->add('fromdate_time', 'A leave application already exists for this period.');
                }
            }

            // 2. Attachment required days validation
            if ($leave_type_id && $fromDateTime) {
                $leaveType = LeaveType::find($leave_type_id);
                if ($leaveType && ($leaveType->attachment_required == 1 || $leaveType->attachment_required_days > 0)) {
                    $selectedDays = 0.5; // default for halfday
                    if ($halfFullDay === 'fullday') {
                        try {
                            $fromDate = \Carbon\Carbon::parse($fromDateTime)->startOfDay();
                            $toDate = $toDateTime ? \Carbon\Carbon::parse($toDateTime)->startOfDay() : $fromDate;
                            $selectedDays = $fromDate->diffInDays($toDate) + 1;
                        } catch (\Exception $e) {
                            $selectedDays = 1;
                        }
                    }

                    $isMandatory = ($leaveType->attachment_required == 1) || ($leaveType->attachment_required_days > 0 && $selectedDays > $leaveType->attachment_required_days);

                    if ($isMandatory) {
                        // Check if file is uploaded or already exists (for edit)
                        $hasFile = $this->hasFile('attachment');
                        $alreadyHasFile = false;
                        if ($edit_id) {
                            $leaveApp = LeaveApplication::find($edit_id);
                            if ($leaveApp && $leaveApp->attachment) {
                                $alreadyHasFile = true;
                            }
                        }

                        if (!$hasFile && !$alreadyHasFile) {
                            $errorMsg = ($leaveType->attachment_required == 1)
                                ? 'Attachment is mandatory for ' . $leaveType->full_name . '.'
                                : 'Attachment is mandatory because leave duration (' . $selectedDays . ' days) exceeds the limit of ' . $leaveType->attachment_required_days . ' days for ' . $leaveType->full_name . '.';
                            $validator->errors()->add('attachment', $errorMsg);
                        }
                    }
                }
            }

            // 3. Carry Forward leave limit validation (Max 7 Days)
            if ($leave_type_id && $fromDateTime) {
                $leaveType = LeaveType::find($leave_type_id);
                if ($leaveType && $leaveType->carry_forward == 1) {
                    $selectedDays = 0.5; // default for halfday
                    if ($halfFullDay === 'fullday') {
                        try {
                            $fromDate = \Carbon\Carbon::parse($fromDateTime)->startOfDay();
                            $toDate = $toDateTime ? \Carbon\Carbon::parse($toDateTime)->startOfDay() : $fromDate;
                            $selectedDays = $fromDate->diffInDays($toDate) + 1;
                        } catch (\Exception $e) {
                            $selectedDays = 1;
                        }
                    }

                    if ($selectedDays > 7) {
                        $validator->errors()->add('fromdate_time', 'Carry Forward leaves cannot be taken for more than 7 days in a single application.');
                    }
                }
            }

            // 4. Monthly leave limit validation
            if ($leave_type_id && $fromDateTime && $employee_id) {
                $leaveType = LeaveType::find($leave_type_id);
                if ($leaveType && $leaveType->count > 0) {
                    $selectedDays = 0.5; // default for halfday
                    if ($halfFullDay === 'fullday') {
                        try {
                            $fromDate = \Carbon\Carbon::parse($fromDateTime)->startOfDay();
                            $toDate = $toDateTime ? \Carbon\Carbon::parse($toDateTime)->startOfDay() : $fromDate;
                            $selectedDays = $fromDate->diffInDays($toDate) + 1;
                        } catch (\Exception $e) {
                            $selectedDays = 1;
                        }
                    }

                    try {
                        $fromDateObj = \Carbon\Carbon::parse($fromDateTime);
                        $targetYear = $fromDateObj->year;
                        $targetMonth = $fromDateObj->month; // 1 to 12

                        $monthlyAccrual = (float)$leaveType->count / 12;

                        if ($leaveType->carry_forward == 1) {
                            // CASE A: Carry Forward is enabled (Accrual / Cumulative)
                            if ($targetMonth >= 4) {
                                $completedMonths = $targetMonth - 4;
                                $fyStart = \Carbon\Carbon::create($targetYear, 4, 1)->startOfDay();
                            } else {
                                $completedMonths = $targetMonth + 8;
                                $fyStart = \Carbon\Carbon::create($targetYear - 1, 4, 1)->startOfDay();
                            }

                            $completedMonthsAccrual = $monthlyAccrual * $completedMonths;

                            // Include current month accrual (full if day >= 15, else half accrual)
                            $currentMonthAccrual = ($fromDateObj->day >= 15) ? $monthlyAccrual : ($monthlyAccrual / 2.0);

                            $accumulatedLimit = $completedMonthsAccrual + $currentMonthAccrual;
                            // Round to nearest 0.5 for consistency with balance display
                            $accumulatedLimit = round($accumulatedLimit * 2) / 2;

                            $targetMonthEnd = $fromDateObj->copy()->endOfMonth();

                            // Calculate total leaves already taken/applied in the same financial year up to (and including) the target month
                            $existingDaysQuery = LeaveApplication::where('employee_id', $employee_id)
                                ->where('leave_type_id', $leave_type_id)
                                ->where('status', '!=', 'rejected')
                                ->whereBetween('fromdate_time', [$fyStart, $targetMonthEnd]);

                            if ($edit_id) {
                                $existingDaysQuery->where('id', '!=', $edit_id);
                            }

                            $existingApps = $existingDaysQuery->get();
                            $totalExistingDays = 0;
                            foreach ($existingApps as $app) {
                                $days = 0.5;
                                if ($app->halfday_fullday === 'fullday') {
                                    try {
                                        $fDate = \Carbon\Carbon::parse($app->fromdate_time)->startOfDay();
                                        $tDate = $app->todate_time ? \Carbon\Carbon::parse($app->todate_time)->startOfDay() : $fDate;
                                        $days = $fDate->diffInDays($tDate) + 1;
                                    } catch (\Exception $e) {
                                        $days = 1;
                                    }
                                }
                                $totalExistingDays += $days;
                            }

                            $totalRequestedDays = $totalExistingDays + $selectedDays;

                            if ($totalRequestedDays > $accumulatedLimit) {
                                $validator->errors()->add('fromdate_time', "As Carry Forward is enabled for {$leaveType->full_name}, you have accrued a maximum of {$accumulatedLimit} leaves up to this month (Accrual rate: {$monthlyAccrual} per month). You have already taken/applied for {$totalExistingDays} days in or before this month.");
                            }
                        } else {
                            // CASE B: Carry Forward is disabled (Strict per-month limit, e.g. Max 2 per month)
                            // Calculate total leaves already taken/applied strictly in the target calendar month
                            $existingDaysQuery = LeaveApplication::where('employee_id', $employee_id)
                                ->where('leave_type_id', $leave_type_id)
                                ->where('status', '!=', 'rejected')
                                ->whereYear('fromdate_time', $targetYear)
                                ->whereMonth('fromdate_time', $targetMonth);

                            if ($edit_id) {
                                $existingDaysQuery->where('id', '!=', $edit_id);
                            }

                            $existingApps = $existingDaysQuery->get();
                            $totalExistingDays = 0;
                            foreach ($existingApps as $app) {
                                $days = 0.5;
                                if ($app->halfday_fullday === 'fullday') {
                                    try {
                                        $fDate = \Carbon\Carbon::parse($app->fromdate_time)->startOfDay();
                                        $tDate = $app->todate_time ? \Carbon\Carbon::parse($app->todate_time)->startOfDay() : $fDate;
                                        $days = $fDate->diffInDays($tDate) + 1;
                                    } catch (\Exception $e) {
                                        $days = 1;
                                    }
                                }
                                $totalExistingDays += $days;
                            }

                            $totalRequestedDays = $totalExistingDays + $selectedDays;

                            if ($totalRequestedDays > $monthlyAccrual) {
                                $validator->errors()->add('fromdate_time', "As Carry Forward is not enabled for {$leaveType->full_name}, you can only take a maximum of {$monthlyAccrual} leaves in a single month (Total Count {$leaveType->count} / 12). You have already taken/applied for {$totalExistingDays} days in this month.");
                            }
                        }
                    } catch (\Exception $e) {
                        // Skip if date parsing failed
                    }
                }
            }

            // 5. C-Off leave balance validation
            if ($leave_type_id && $fromDateTime && $employee_id) {
                $leaveType = LeaveType::find($leave_type_id);
                if ($leaveType && (strtolower($leaveType->sort_name) === 'c-off' || strtolower($leaveType->sort_name) === 'coff' || strtolower($leaveType->full_name) === 'compensatory off')) {
                    $selectedDays = 0.5; // default for halfday
                    if ($halfFullDay === 'fullday') {
                        try {
                            $fromDate = \Carbon\Carbon::parse($fromDateTime)->startOfDay();
                            $toDate = $toDateTime ? \Carbon\Carbon::parse($toDateTime)->startOfDay() : $fromDate;
                            $selectedDays = $fromDate->diffInDays($toDate) + 1;
                        } catch (\Exception $e) {
                            $selectedDays = 1;
                        }
                    }

                    $employee = Employee::find($employee_id);
                    if ($employee) {
                        $available = $employee->getAvailableCoffCount($fromDateTime, $edit_id);







                        if ($selectedDays > $available) {
                            $validator->errors()->add('fromdate_time', "You only have {$available} Compensatory Off (C-Off) leaves available. You are trying to apply for {$selectedDays} days.");
                        }
                    }
                }
            }

            // 6. Available leave balance validation (same logic as displayed balance)
            if ($leave_type_id && $fromDateTime && $employee_id) {
                $leaveType = LeaveType::find($leave_type_id);
                if ($leaveType) {
                    $selectedDays = 0.5;
                    if ($halfFullDay === 'fullday') {
                        try {
                            $fromDate = \Carbon\Carbon::parse($fromDateTime)->startOfDay();
                            $toDate = $toDateTime ? \Carbon\Carbon::parse($toDateTime)->startOfDay() : $fromDate;
                            $selectedDays = $fromDate->diffInDays($toDate) + 1;
                        } catch (\Exception $e) {
                            $selectedDays = 1;
                        }
                    }

                    $employee = Employee::find($employee_id);
                    if ($employee) {
                        $available = $employee->getAvailableLeaveBalance($leave_type_id, $edit_id);
                        if ($selectedDays > $available) {
                            $validator->errors()->add('fromdate_time', "You only have {$available} {$leaveType->full_name} days available. You are trying to apply for {$selectedDays} days.");
                        }
                    }
                }
            }

        });
    }
}
