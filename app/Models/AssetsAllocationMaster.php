<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetsAllocationMaster extends Model
{
     use SoftDeletes;

    protected $table = 'assets_allocation_masters';

    protected $fillable = [
        'company_id',
        'name',
        'display_order',
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
}
