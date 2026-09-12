<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shift extends Model
{
    use SoftDeletes;

    protected $table = 'shifts';

    protected $fillable = [
        'company_id',
        'branch_id',
        'name',
        'punch_in_minimum',
        'punch_out',
        'auto_punch_out',
        
        'working_hour',
        'breaking_hour',
        'half_day_hour',
        'present_day_hour',

        'in_out_grace_period',
        'grace_period',
        'employee_max_working_hours',
        'monitor_by',
        'status',
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

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

    public function monitor_by_detail()
    {
        return $this->belongsTo(Employee::class, 'monitor_by', 'id');
    }
}
