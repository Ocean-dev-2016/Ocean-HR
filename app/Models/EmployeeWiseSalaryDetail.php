<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeWiseSalaryDetail extends Model
{
    use SoftDeletes;

    protected $table = 'employee_wise_salary_details';

    protected $fillable = [
        'company_id',
        'employee_id',
        'salary_classification',
        'week_off',
        'overtime',
        'is_welfare_fund_applied',
        'welfare_fund_amount',
        'leave_elegiblity',
        'sandwich_rule_flag',
        'sandwich_rule_applied_on',
        'sandwich_rule_type',
        'is_bonus_applied',
        'salary_calculation_month_count',
        'pf_type',
        'pf',
        'pf_percentage',
        'pradhanmantri_pf',
        'pradhanmantri_pf_percentage',
        'tds',
        'tds_percentage',
        'insurance',
        'insurance_amount',
        'pt',
        'pt_amount',
        'is_esi_company_side',
        'esi_company_side_percentage',
        'esi_employee_side',
        'esi_employee_side_percentage',
        'gratuity_calculation',
        'previous_gross_salary',
        'basic_da',
        'hra',
        'conveyance_allowance',
        'medical_allowance',
        'special_allowance',
        'ctc',
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

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }
}
