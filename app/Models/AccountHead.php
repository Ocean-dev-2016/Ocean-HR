<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountHead extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'name',
        'account_head_id',
        'date',
        'description',
        'debit_amount',
        'credit_amount',
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
    public function setDateAttribute($value)
    {
        $this->attributes['date'] = $value ? (preg_match('/^\d{2}-\d{2}-\d{4}$/', $value) ? \Carbon\Carbon::createFromFormat('d-m-Y', $value)->format('Y-m-d') : \Carbon\Carbon::parse($value)->format('Y-m-d')) : null;
    }

    public function getDateAttribute($value)
    {
        return $value ? \Carbon\Carbon::parse($value)->format('d-m-Y') : null;
    }

    public function setToDateAttribute($value)
    {
        $this->attributes['to_date'] = $value ? (preg_match('/^\d{2}-\d{2}-\d{4}$/', $value) ? \Carbon\Carbon::createFromFormat('d-m-Y', $value)->format('Y-m-d') : \Carbon\Carbon::parse($value)->format('Y-m-d')) : null;
    }

    public function getToDateAttribute($value)
    {
        return $value ? \Carbon\Carbon::parse($value)->format('d-m-Y') : null;
    }

    public function setFromDateAttribute($value)
    {
        $this->attributes['from_date'] = $value ? (preg_match('/^\d{2}-\d{2}-\d{4}$/', $value) ? \Carbon\Carbon::createFromFormat('d-m-Y', $value)->format('Y-m-d') : \Carbon\Carbon::parse($value)->format('Y-m-d')) : null;
    }

    public function getFromDateAttribute($value)
    {
        return $value ? \Carbon\Carbon::parse($value)->format('d-m-Y') : null;
    }
}
