<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmploymentDetail extends Model
{
    use SoftDeletes;

    protected $table = 'employment_details';

    protected $fillable = [
        'company_id',
        'employee_id',
        'designation_type',
        'designation_id',

        'department_id',
        'sub_department_id',
        'process_id',
        'date_of_joining',
        'employment_confirmation_date',
        'employee_pf_no',
        'uan_no',
        'payment_mode',
        'employment_type',
        'shift',
        'outdoor_attendance',
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

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }

    public function subdepartment()
    {
        return $this->belongsTo(SubDepartment::class, 'sub_department_id', 'id');
    }

    public function process()
    {
        return $this->belongsTo(Process::class, 'process_id', 'id');
    }

    public function employee_type()
    {
        return $this->belongsTo(EmployeeType::class, 'employment_type', 'id');
    }

    public function shiftDetail()
    {
        return $this->belongsTo(Shift::class, 'shift', 'id');
    }
}
