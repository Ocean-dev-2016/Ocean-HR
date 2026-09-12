<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveType extends Model
{
    use SoftDeletes;

    protected $table = 'leave_types';

    protected $fillable = [
        'company_id',
        'sort_name',
        'full_name',
        'count',
        'mode',
        'carry_forward',
        'attachment_required',
        'attachment_required_days',
        'status',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
        'deleted_by',
        'deleted_at'
    ];

    public $appends = ["mode_name"];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function getModeNameAttribute()
    {
        # mode_name
        $mode_name = "N/A";
        if ($this->mode == 0) {
            $mode_name = "Employee Pay";
        } else if ($this->mode == 1) {
            $mode_name = "Company Pay";
        } else {

        }
        return $mode_name;
    }
}
