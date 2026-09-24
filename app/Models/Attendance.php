<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Attendance extends Model
{
    use SoftDeletes;

    protected $table = 'attendances';
    protected $fillable = [
        'company_id',
        'employee_id',
        'shift_id',
        'attendance_date',
        'create_date',
        'punch_in_time',
        'punch_image',
        'attendace_type',
        'remark',
        'status',
        'punch_id',
        'txn_id',
        'records_source',
        'requested_data',
        'device_serial',
        'device_ip',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
        'deleted_by',
        'deleted_at'
    ];
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }
    public function shift()
    {
        return $this->belongsTo(Shift::class, 'shift_id', 'id');
    }
    public function creator()
    {
        return $this->belongsTo(Employee::class, 'created_by', 'id');
    }
    public static $AttendaceType = [
        'in' => "In",
        'out' => "Out",
        // 'day' => "Day",
    ];

    public function getPunchImageUrlAttribute()
    {
        if (!$this->punch_image) {
            return null;
        }
        if (filter_var($this->punch_image, FILTER_VALIDATE_URL)) {
            return $this->punch_image;
        }
        return asset($this->punch_image);
    }
}
