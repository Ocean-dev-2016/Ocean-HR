<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Loan extends Model
{
    use HasFactory, SoftDeletes;

    public $table = "loans";

    protected $fillable = [
        'company_id',
        'employee_id',
        'loan_type_id',
        'loan_amount',
        'balance_amount',
        'emi_amount',
        'total_installments',
        'remaining_installments',
        'interest_rate',
        'interest_type',
        'loan_date',
        'status',
        'remark',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $attributes = [
        'interest_rate' => 0,     // ✅ default 0
        'interest_type' => 'flat' // ✅ default flat
    ];

    protected $casts = [
        'loan_date' => 'date',
    ];


    public static $loanStatus = [
        'pending' => "Pending",
        'approved' => "Approved",
        'rejected' => "Rejected",
        'closed' => "Closed"
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function loan_type()
    {
        return $this->belongsTo(LoanTypes::class);
    }

    public function repayments()
    {
        return $this->hasMany(LoanRepayments::class);
    }

    public function next_pending_emi()
    {
        return $this->hasOne(LoanRepayments::class)->where('status', 'pending');
    }

    public static function calculateSchedule($loanAmount, $interestRate, $installments, $interestType)
    {
        $schedule = [];
        $today = now();

        // If interest rate is 0 or null, treat as no-interest loan
        $isInterestFree = !$interestRate || $interestRate == 0;

        if ($interestType === 'flat' || $isInterestFree) {
            // Flat Interest or Interest-Free
            $principalPerInstallment = $loanAmount / $installments;
            $interestPerInstallment = $isInterestFree ? 0 : ($loanAmount * ($interestRate / 100)) / $installments;

            for ($i = 1; $i <= $installments; $i++) {
                $dueDate = $today->copy()->addMonths($i);

                $schedule[] = [
                    'installment_no' => $i,
                    'due_date' => $dueDate->format('d-M-Y'),
                    'principal' => round($principalPerInstallment, 2),
                    'interest' => round($interestPerInstallment, 2),
                    'installment_amount' => round($principalPerInstallment + $interestPerInstallment, 2),
                ];
            }
        } else {
            // Reducing Balance EMI
            $monthlyRate = ($interestRate / 100) / 12;
            $emi = $loanAmount * $monthlyRate * pow(1 + $monthlyRate, $installments) /
                (pow(1 + $monthlyRate, $installments) - 1);

            $balance = $loanAmount;

            for ($i = 1; $i <= $installments; $i++) {
                $interest = $balance * $monthlyRate;
                $principal = $emi - $interest;
                $balance -= $principal;

                $dueDate = $today->copy()->addMonths($i);

                $schedule[] = [
                    'installment_no' => $i,
                    'due_date' => $dueDate->format('Y-m-d'),
                    'principal' => round($principal, 2),
                    'interest' => round($interest, 2),
                    'installment_amount' => round($emi, 2),
                ];
            }
        }

        return $schedule;
    }
}
