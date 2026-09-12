<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Process extends Model
{
    use SoftDeletes;
    protected $table = 'processes';

    protected $fillable = [
        'company_id',
        'department_id',
        'sub_department_id',
        'name',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }
    public function subdepartment()
    {
        return $this->belongsTo(SubDepartment::class, 'sub_department_id', 'id');
    }
}
