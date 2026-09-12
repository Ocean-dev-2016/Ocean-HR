<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class PaymentReceipt extends Model
{
    use SoftDeletes;
    public $table = "payment_receipts";
    protected $fillable = [
        'company_id',
        'branch_id',
        'department',
        'employee_id',
        'effect_on_month',
        'effect_of_year',
        'date',
        'amount',
        'account_head_id',
        'payment_mode',
        'payment_type',
        'upi_no',
        'cheque_no',
        'receipt_no',
        'remark',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public static $payment_type = [
        "cash" => "Cash",
        "cheque" => "Cheque",
        "bank_transfer" => "Bank Transfer",
        "upi" => "UPI",


    ];

    public static $payment_mode = [
        "credit" => "Credit",
        "debit" => "Debit",
    ];
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }
    public function departments()
    {
        return $this->belongsTo(Department::class, 'department', 'id');
    }

    // public function accountHead()
    // {
    //     return $this->belongsTo(AccountHead::class, 'account_head_id', 'id');
    // }
}
