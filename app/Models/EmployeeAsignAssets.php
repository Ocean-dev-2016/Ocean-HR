<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeAsignAssets extends Model
{
     use SoftDeletes;

    protected $table = 'employee_asign_assets';

    protected $fillable = [
        'company_id',
        'employee_id',
        'assets_id',
        'date',
        'reference_no',
        'attachment',
        'descrption',
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
     public function assets()
    {
        return $this->belongsTo(AssetsAllocationMaster::class, 'assets_id', 'id');
    }
     public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }
}
