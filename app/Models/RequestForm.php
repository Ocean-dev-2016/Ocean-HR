<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RequestForm extends Model
{
    use SoftDeletes;

    protected $table = 'request_forms';
    protected $fillable = [
        'company_id',
        'request_from_employee_name',
        'request_to_employee_name',
        'attechment',
        'request_description',
        'status',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
        'deleted_by',
        'deleted_at'
    ];
    protected $appends = ['attechment_url'];

    public function getAttechmentUrlAttribute()
    {
        if ($this->attechment && file_exists(public_path($this->attechment))) {
            return asset($this->attechment);
        }
        return null;
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function requestFromEmployee()
    {
        return $this->belongsTo(Employee::class, 'request_from_employee_name', 'id');
    }

    public function requestToEmployee()
    {
        return $this->belongsTo(Employee::class, 'request_to_employee_name', 'id');
    }
}
