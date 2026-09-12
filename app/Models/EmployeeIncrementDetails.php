<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeIncrementDetails extends Model
{
    use SoftDeletes;

    protected $table = 'employee_increment_details';

    protected $fillable = [
        'company_id',
        'employee_id',
        'icrement_date',
        'basic_da',
        'hra',
        'conveyance_allowance',
        'medical_allowance',
        'special_allowance',
        'pf',
        'effective_month',
        'effective_year',
        'designation_id',
        'per_day_salary',
        'per_hour_salary',
        'remark',
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
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }
     public function designation()
    {
        return $this->belongsTo(Designation::class, 'designation_id', 'id');
    }
}
