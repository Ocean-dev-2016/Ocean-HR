<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Salary extends Model
{
    use SoftDeletes;

    protected $table = 'salaries';

    protected $fillable = [
        'company_id',
        'branch_id',
        'department_id',
        'employee_id',
        'year',
        'month',
        'added_date',
        'calculate_days',
        'total_present_day',
        'adjustment_days',
        'adjustment_remark',
        'half_day',
        'holiday',
        'compensation_hour',
        'compensation_day',
        'total_week_off',
        'working_week_off',
        'total_sandwich_leave',
        'total_leave',
        'total_company_pay_leave',
        'total_employee_pay_leave',
        'total_absent',
        'total_day',
        'working_hour',
        'ctc',
        'gross_salary',
        'given_calculate_salary',
        'per_day_salary',
        'conveyance_allowance',
        'medical_allowance',
        'special_allowance',
        'present_day_amount',
        'employee_weekoff_amount',
        'working_weekoff_amount',
        'company_pay_leave_amount',
        'employee_pay_leave_amount',
        'fxs_basic',
        'fxs_hra',
        'fxs_other',
        'fxs_total_earning',
        'dws_basic',
        'dws_da',
        'dws_other',
        'dws_total_earning',
        'actual_total_ot_hours',
        'earn_ot_hours',
        'earn_ot_payable_amt',
        'earn_ot_days',
        'earn_performation_incentive',
        'bonus_amount',
        'bonus_temp_amount',
        'bonus_amount_adjustment',
        'earn_sub_total',
        'total_earning',
        'ded_employee_pf',
        'ded_pradhan_mantri_pf',
        'ded_esi_employee',
        'ded_esi_company',
        'ded_pt',
        'ded_insurance',
        'ded_advance',
        'advance_amount_adjustment',
        'ded_tds',
        'tds_amount_adjustment',
        'ded_wf',
        'ded_loan_amount',
        'loan_amount_adjustment',
        'ded_other',
        'total_deduction',
        'ded_other_remark',
        'net_bank_pay',
        'additional_ot_hour',
        'total_acc_ot_plus_add_ot',
        'status',
        'calculation_date',
        'attendance_snapshot_date',
        'attendance_records_count',
        'leave_snapshot_date',
        'leave_records_count',
        'holiday_snapshot_date',
        'holiday_records_count',
        'salary_detail_snapshot_date',
        'data_modified',
        'data_modification_details',
        'is_locked',
        'locked_at',
        'locked_by',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
        'deleted_by',
        'deleted_at'
    ];

    protected $casts = [
        'calculation_date' => 'datetime',
        'attendance_snapshot_date' => 'datetime',
        'leave_snapshot_date' => 'datetime',
        'holiday_snapshot_date' => 'datetime',
        'salary_detail_snapshot_date' => 'datetime',
        'locked_at' => 'datetime',
        'data_modified' => 'boolean',
        'is_locked' => 'boolean',
        'data_modification_details' => 'array',
    ];

    /**
     * Relationship with Company
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    /**
     * Relationship with Branch
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

    /**
     * Relationship with Department
     */
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }

    /**
     * Relationship with Employee
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    /**
     * Get month name attribute
     */
    public function getMonthNameAttribute()
    {
        $months = [
            1 => 'January',
            2 => 'February',
            3 => 'March',
            4 => 'April',
            5 => 'May',
            6 => 'June',
            7 => 'July',
            8 => 'August',
            9 => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December'
        ];
        return $months[$this->month] ?? $this->month;
    }

    /**
     * Get period display attribute
     */
    public function getPeriodDisplayAttribute()
    {
        return $this->month_name . ' ' . $this->year;
    }

    /**
     * Check if source data has been modified after calculation
     */
    public function checkDataIntegrity()
    {
        $modifications = [];
        $hasModifications = false;

        // Check attendance data
        if ($this->attendance_snapshot_date) {
            $latestAttendance = \App\Models\Attendance::where('company_id', $this->company_id)
                ->where('employee_id', $this->employee_id)
                ->whereBetween('attendance_date', [
                    \Carbon\Carbon::createFromDate($this->year, $this->month, 1)->startOfMonth(),
                    \Carbon\Carbon::createFromDate($this->year, $this->month, 1)->endOfMonth()
                ])
                ->orderBy('updated_at', 'desc')
                ->first();

            if ($latestAttendance && $latestAttendance->updated_at > $this->attendance_snapshot_date) {
                $modifications['attendance'] = [
                    'modified' => true,
                    'last_modified' => $latestAttendance->updated_at->format('Y-m-d H:i:s'),
                    'snapshot_date' => $this->attendance_snapshot_date->format('Y-m-d H:i:s')
                ];
                $hasModifications = true;
            }

            // Check attendance count
            $currentCount = \App\Models\Attendance::where('company_id', $this->company_id)
                ->where('employee_id', $this->employee_id)
                ->whereBetween('attendance_date', [
                    \Carbon\Carbon::createFromDate($this->year, $this->month, 1)->startOfMonth(),
                    \Carbon\Carbon::createFromDate($this->year, $this->month, 1)->endOfMonth()
                ])
                ->count();

            if ($currentCount != $this->attendance_records_count) {
                $modifications['attendance']['count_changed'] = true;
                $modifications['attendance']['original_count'] = $this->attendance_records_count;
                $modifications['attendance']['current_count'] = $currentCount;
                $hasModifications = true;
            }
        }

        // Check leave data
        if ($this->leave_snapshot_date) {
            $latestLeave = \App\Models\LeaveApplication::where('company_id', $this->company_id)
                ->where('employee_id', $this->employee_id)
                ->where('status', 'approved')
                ->where(function ($q) {
                    $startDate = \Carbon\Carbon::createFromDate($this->year, $this->month, 1)->startOfMonth();
                    $endDate = \Carbon\Carbon::createFromDate($this->year, $this->month, 1)->endOfMonth();
                    $q->whereBetween('fromdate_time', [$startDate, $endDate])
                        ->orWhereBetween('todate_time', [$startDate, $endDate]);
                })
                ->orderBy('updated_at', 'desc')
                ->first();

            if ($latestLeave && $latestLeave->updated_at > $this->leave_snapshot_date) {
                $modifications['leave'] = [
                    'modified' => true,
                    'last_modified' => $latestLeave->updated_at->format('Y-m-d H:i:s'),
                    'snapshot_date' => $this->leave_snapshot_date->format('Y-m-d H:i:s')
                ];
                $hasModifications = true;
            }

            // Check leave count
            $currentLeaveCount = \App\Models\LeaveApplication::where('company_id', $this->company_id)
                ->where('employee_id', $this->employee_id)
                ->where('status', 'approved')
                ->where(function ($q) {
                    $startDate = \Carbon\Carbon::createFromDate($this->year, $this->month, 1)->startOfMonth();
                    $endDate = \Carbon\Carbon::createFromDate($this->year, $this->month, 1)->endOfMonth();
                    $q->whereBetween('fromdate_time', [$startDate, $endDate])
                        ->orWhereBetween('todate_time', [$startDate, $endDate]);
                })
                ->count();

            if ($currentLeaveCount != $this->leave_records_count) {
                $modifications['leave']['count_changed'] = true;
                $modifications['leave']['original_count'] = $this->leave_records_count;
                $modifications['leave']['current_count'] = $currentLeaveCount;
                $hasModifications = true;
            }
        }

        // Check holiday data
        if ($this->holiday_snapshot_date) {
            $latestHoliday = \App\Models\Holiday::where('company_id', $this->company_id)
                ->where(function ($q) {
                    $startDate = \Carbon\Carbon::createFromDate($this->year, $this->month, 1)->startOfMonth();
                    $endDate = \Carbon\Carbon::createFromDate($this->year, $this->month, 1)->endOfMonth();
                    $q->whereBetween('from_date', [$startDate, $endDate])
                        ->orWhereBetween('to_date', [$startDate, $endDate]);
                })
                ->orderBy('updated_at', 'desc')
                ->first();

            if ($latestHoliday && $latestHoliday->updated_at > $this->holiday_snapshot_date) {
                $modifications['holiday'] = [
                    'modified' => true,
                    'last_modified' => $latestHoliday->updated_at->format('Y-m-d H:i:s'),
                    'snapshot_date' => $this->holiday_snapshot_date->format('Y-m-d H:i:s')
                ];
                $hasModifications = true;
            }
        }

        // Check salary detail
        if ($this->salary_detail_snapshot_date) {
            $salaryDetail = \App\Models\EmployeeWiseSalaryDetail::where('company_id', $this->company_id)
                ->where('employee_id', $this->employee_id)
                ->first();

            if ($salaryDetail && $salaryDetail->updated_at > $this->salary_detail_snapshot_date) {
                $modifications['salary_detail'] = [
                    'modified' => true,
                    'last_modified' => $salaryDetail->updated_at->format('Y-m-d H:i:s'),
                    'snapshot_date' => $this->salary_detail_snapshot_date->format('Y-m-d H:i:s')
                ];
                $hasModifications = true;
            }
        }

        // Update modification status
        if ($hasModifications) {
            $this->data_modified = true;
            $this->data_modification_details = $modifications;
            $this->save();
        }

        return [
            'has_modifications' => $hasModifications,
            'modifications' => $modifications
        ];
    }

    /**
     * Lock salary to prevent recalculation
     */
    public function lock($userId = null)
    {
        $this->is_locked = true;
        $this->locked_at = now();
        $this->locked_by = $userId;
        $this->save();
    }

    /**
     * Unlock salary
     */
    public function unlock()
    {
        $this->is_locked = false;
        $this->locked_at = null;
        $this->locked_by = null;
        $this->save();
    }
}
