<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bonus extends Model
{
    use SoftDeletes;

    protected $table = 'bonuses';

    protected $fillable = [
        'company_id',
        'year',
        'month',
        'amount',
        'branch',
        'employee',
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
        return $this->belongsTo(Branch::class, 'branch', 'id');
    }
    public function employeeRelation()
    {
        return $this->belongsTo(Employee::class, 'employee', 'id');
    }
}
