<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeEducationExperienceDetail extends Model
{
     use SoftDeletes;

    protected $table = 'employee_education_experience_details';

    protected $fillable = [
        'company_id',
        'employee_id',
        'degree',
        'institution_name',
        'month_of_passing_year',
        'class_or_mark',

        'company_name',
        'joining_date',
        'left_date',
        'designation',
        'ctc_salary',

        'department_name',
        'designation_name',
        'unit_change',
        'effective_date',

        'document_type',
        'document_name',
        'attachment',

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
}
