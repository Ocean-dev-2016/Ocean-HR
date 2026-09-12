<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OperationsRateList extends Model
{
    use SoftDeletes;

    protected $table = 'operations_rate_lists';

    protected $fillable = [
        'company_id',
        'status',
        'operation',
        'product_id',
        'grade_id',
        'employee_id',
        'contract_process_id',
        'month',
        'year',
        'day_1',
        'day_1_r',
        'day_1_ot',
        'day_2',
        'day_2_r',
        'day_2_ot',
        'day_3',
        'day_3_r',
        'day_3_ot',
        'day_4',
        'day_4_r',
        'day_4_ot',
        'day_5',
        'day_5_r',
        'day_5_ot',
        'day_6',
        'day_6_r',
        'day_6_ot',
        'day_7',
        'day_7_r',
        'day_7_ot',
        'day_8',
        'day_8_r',
        'day_8_ot',
        'day_9',
        'day_9_r',
        'day_9_ot',
        'day_10',
        'day_10_r',
        'day_10_ot',
        'day_11',
        'day_11_r',
        'day_11_ot',
        'day_12',
        'day_12_r',
        'day_12_ot',
        'day_13',
        'day_13_r',
        'day_13_ot',
        'day_14',
        'day_14_r',
        'day_14_ot',
        'day_15',
        'day_15_r',
        'day_15_ot',
        'day_16',
        'day_16_r',
        'day_16_ot',
        'day_17',
        'day_17_r',
        'day_17_ot',
        'day_18',
        'day_18_r',
        'day_18_ot',
        'day_19',
        'day_19_r',
        'day_19_ot',
        'day_20',
        'day_20_r',
        'day_20_ot',
        'day_21',
        'day_21_r',
        'day_21_ot',
        'day_22',
        'day_22_r',
        'day_22_ot',
        'day_23',
        'day_23_r',
        'day_23_ot',
        'day_24',
        'day_24_r',
        'day_24_ot',
        'day_25',
        'day_25_r',
        'day_25_ot',
        'day_26',
        'day_26_r',
        'day_26_ot',
        'day_27',
        'day_27_r',
        'day_27_ot',
        'day_28',
        'day_28_r',
        'day_28_ot',
        'day_29',
        'day_29_r',
        'day_29_ot',
        'day_30',
        'day_30_r',
        'day_30_ot',
        'day_31',
        'day_31_r',
        'day_31_ot',
        'total_qty',
        'rate',
        'total_amount',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public static $statuses = [
        'CLEANING',
        'Lathe Employee wise',
        'GRINDING',
        'CNC',
        'ARGON WELDING',
        'BUFF',
        'COATING',
        'CORE',
        'ASS-1',
        'ASS-2',
        'RRL',
        'BUTTERFLY',
        'FLEXIBLE',
        'FOUNDRY'
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function grade()
    {
        return $this->belongsTo(Grade::class, 'grade_id', 'id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function contractProcess()
    {
        return $this->belongsTo(ContractProcess::class, 'contract_process_id', 'id');
    }
}
