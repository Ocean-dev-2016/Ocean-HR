<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LoanRepayments extends Model
{
    use HasFactory, SoftDeletes;

    public $table = "loan_repayments";

    protected $fillable = [
        'loan_id',
        'loan_amount',
        'interest',
        'installment_amount',
        'due_date',
        'paid_date',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'due_date' => 'date',
        'paid_date' => 'datetime',
    ];

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }
}
