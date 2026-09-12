<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('salaries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('employee_id')->nullable();

            $table->year('year')->nullable();
            $table->string('month',20)->nullable();

            $table->string('added_date')->nullable();
            $table->decimal('calculate_days', 8, 2)->nullable();
            $table->decimal('total_present_day', 8, 2)->nullable();
            $table->decimal('adjustment_days', 8, 2)->nullable();
            $table->text('adjustment_remark')->nullable();

            $table->decimal('half_day', 8, 2)->nullable();
            $table->decimal('holiday', 8, 2)->nullable();
            $table->decimal('compensation_hour', 8, 2)->nullable();
            $table->decimal('compensation_day', 8, 2)->nullable();
            $table->decimal('total_week_off', 8, 2)->nullable();
            $table->decimal('total_sandwich_leave', 8, 2)->nullable();
            $table->decimal('total_leave', 8, 2)->nullable();
            $table->decimal('total_company_pay_leave', 8, 2)->nullable();
            $table->decimal('total_employee_pay_leave', 8, 2)->nullable();
            $table->decimal('total_absent', 8, 2)->nullable();
            $table->decimal('total_day', 8, 2)->nullable();

            $table->time('working_hour')->nullable();
            $table->decimal('ctc', 12, 2)->nullable();
            $table->decimal('gross_salary', 12, 2)->nullable();
            $table->decimal('given_calculate_salary', 12, 2)->nullable();
            $table->decimal('per_day_salary', 12, 2)->nullable();

            $table->decimal('conveyance_allowance', 12, 2)->nullable();
            $table->decimal('medical_allowance', 12, 2)->nullable();
            $table->decimal('special_allowance', 12, 2)->nullable();

            $table->decimal('present_day_amount', 12, 2)->nullable();
            $table->decimal('employee_weekoff_amount', 12, 2)->nullable();
            $table->decimal('company_pay_leave_amount', 12, 2)->nullable();
            $table->decimal('employee_pay_leave_amount', 12, 2)->nullable();

            $table->decimal('fxs_basic', 12, 2)->nullable();
            $table->decimal('fxs_hra', 12, 2)->nullable();
            $table->decimal('fxs_other', 12, 2)->nullable();
            $table->decimal('fxs_total_earning', 12, 2)->nullable();

            $table->decimal('dws_basic', 12, 2)->nullable();
            $table->decimal('dws_da', 12, 2)->nullable();
            $table->decimal('dws_other', 12, 2)->nullable();
            $table->decimal('dws_total_earning', 12, 2)->nullable();

            $table->decimal('actual_total_ot_hours', 8, 2)->nullable();
            $table->decimal('earn_ot_hours', 8, 2)->nullable();
            $table->decimal('earn_ot_payable_amt', 12, 2)->nullable();
            $table->decimal('earn_ot_days', 8, 2)->nullable();

            $table->decimal('earn_performation_incentive', 12, 2)->nullable();
            $table->decimal('bonus_amount', 12, 2)->nullable();
            $table->decimal('bonus_temp_amount', 12, 2)->nullable();
            $table->decimal('bonus_amount_adjustment', 12, 2)->nullable();

            $table->decimal('earn_sub_total', 12, 2)->nullable();
            $table->decimal('total_earning', 12, 2)->nullable();

            $table->decimal('ded_employee_pf', 12, 2)->nullable();
            $table->decimal('ded_pradhan_mantri_pf', 12, 2)->nullable();
            $table->decimal('ded_esi_employee', 12, 2)->nullable();
            $table->decimal('ded_esi_company', 12, 2)->nullable();
            $table->decimal('ded_pt', 12, 2)->nullable();
            $table->decimal('ded_insurance', 12, 2)->nullable();
            $table->decimal('ded_advance', 12, 2)->nullable();
            $table->decimal('advance_amount_adjustment', 12, 2)->nullable();
            $table->decimal('ded_tds', 12, 2)->nullable();
            $table->decimal('tds_amount_adjustment', 12, 2)->nullable();
            $table->decimal('ded_wf', 12, 2)->nullable();
            $table->decimal('ded_loan_amount', 12, 2)->nullable();
            $table->decimal('loan_amount_adjustment', 12, 2)->nullable();
            $table->decimal('ded_other', 12, 2)->nullable();
            $table->decimal('total_deduction', 12, 2)->nullable();
            $table->text('ded_other_remark')->nullable();


            $table->decimal('net_bank_pay', 12, 2)->nullable();
            $table->decimal('additional_ot_hour', 8, 2)->nullable();
            $table->decimal('total_acc_ot_plus_add_ot', 8, 2)->nullable();

            $table->string('status')->default('active')->comment('active, inactive');
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps();
            $table->string('deleted_by')->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salaries');
    }
};
