<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LoanTypes extends Model
{
    use HasFactory, SoftDeletes;

    public $table = "loan_types";

    protected $fillable = [
        'company_id',
        'name',
        'display_order',
        'description',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
