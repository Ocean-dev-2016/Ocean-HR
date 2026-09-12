<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountLedger extends Model
{
    protected $fillable = [
        'company_id',

        'employee_id',
        'account_head_id',
        'entry_date',
        'receipt_id',
        'payment_mode',
        'amount',
        'debit_amount',
        'credit_amount',
        'description',
        'closing_balance',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }
}
