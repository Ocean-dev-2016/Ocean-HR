<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveApplication extends Model
{
    use SoftDeletes;
    public $table = "leave_applications";

    static $folderPath = "leave_applications/";

    protected $fillable = [
        'company_id',
        'branch_id',
        'employee_id',
        'leave_type_id',
        'coff_worked_date',
        'fromdate_time',
        'todate_time',
        'halfday_fullday',
        'singleday_multipleday',
        'firsthalf_secondhalf',
        'leave_reason',
        'attachment',
        'rejection_reason',
        'status',
        'approval_level',
        'supervisor_status',
        'supervisor_approved_by',
        'supervisor_approved_at',
        'supervisor_remark',
        'hod_status',
        'hod_approved_by',
        'hod_approved_at',
        'hod_remark',
        'hr_status',
        'hr_approved_by',
        'hr_approved_at',
        'hr_remark',
        'rejected_by',
        'rejected_by_role',
        'team_person_ids',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $appends = ['attachment_url'];

    public static $leaveForDay = [
        'halfday' => "Half Day",
        'fullday' => "Full Day",
        // 'day' => "Day",
    ];
    public static $leaveByDays = [
        'singleday' => "Single Day",
        'multipleday' => "Mutiple Day",

    ];
    public static $leaveForHalfdays = [
        'firsthalf' => "First Half",
        'secondhalf' => "Second Half",

    ];
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }
    // In Leave model
    public function leave_type()
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id', 'id');
    }
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }


    public function getAttachmentUrlAttribute()
    {
        if ($this->attachment) {
            if (file_exists(public_path($this->attachment))) {
                return asset($this->attachment);
            }
            return public_path($this->attachment);
        }
        return null;
    }

    public function supervisorApprover()
    {
        return $this->belongsTo(Employee::class, 'supervisor_approved_by', 'id');
    }

    public function hodApprover()
    {
        return $this->belongsTo(Employee::class, 'hod_approved_by', 'id');
    }

    public function hrApprover()
    {
        return $this->belongsTo(Employee::class, 'hr_approved_by', 'id');
    }

    public function rejector()
    {
        return $this->belongsTo(Employee::class, 'rejected_by', 'id');
    }

    public function getStageLabel()
    {
        if ($this->status === 'rejected') {
            $by = $this->rejected_by_role ? 'by ' . $this->rejected_by_role : '';
            return 'Rejected ' . $by;
        }
        if ($this->status === 'approved') {
            return 'Approved (Final)';
        }

        return match ((int) $this->approval_level) {
            1 => 'Pending (Supervisor Approval)',
            2 => 'Pending (Dept Head Approval)',
            3 => 'Pending (Main HR Approval)',
            default => 'Pending',
        };
    }
}
