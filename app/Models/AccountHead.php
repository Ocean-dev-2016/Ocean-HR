<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountHead extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'date',
        'description',
        'debit_amount',
        'credit_admount',
        'balance',
        'to_date',
        'from_date',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }
   public function ledgers()
    {
        return $this->hasMany(AccountLedger::class, 'account_head_id', 'id');
    }
}
