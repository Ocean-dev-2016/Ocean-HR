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
        Schema::create('employee_wise_salary_details', function (Blueprint $table) {
            $table->id();
            $table->string('company_id');
            $table->string('employee_id');
            $table->string('salary_classification')->nullable();
            $table->string('week_off')->nullable();
            $table->string('overtime')->nullable();
            $table->string('is_welfare_fund_applied')->nullable();
            $table->string('leave_elegiblity')->nullable();
            $table->string('sandwich_rule_flag')->nullable();
            $table->string('sandwich_rule_applied_on')->nullable();
            $table->string('sandwich_rule_type')->nullable();
            $table->string('is_bonus_applied')->nullable();
            $table->string('salary_calculation_month_count')->nullable();
            $table->string('pf_type')->nullable();
            $table->string('pf')->nullable();
            $table->string('pf_percentage')->nullable();
            $table->string('pradhanmantri_pf')->nullable();
            $table->string('pradhanmantri_pf_percentage')->nullable();
            $table->string('tds')->nullable();
            $table->string('tds_percentage')->nullable();
            $table->string('insurance')->nullable();
            $table->string('insurance_amount')->nullable();
            $table->string('pt')->nullable();
            $table->string('pt_amount')->nullable();
            $table->string('is_esi_company_side')->nullable();
            $table->string('esi_company_side_percentage')->nullable();
            $table->string('esi_employee_side')->nullable();
            $table->string('esi_employee_side_percentage')->nullable();
            $table->string('gratuity_calculation')->nullable();
            $table->string('basic_da')->nullable();
            $table->string('hra')->nullable();
            $table->string('conveyance_allowance')->nullable();
            $table->string('medical_allowance')->nullable();
            $table->string('special_allowance')->nullable();
            $table->string('ctc')->nullable();
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
        Schema::dropIfExists('employee_wise_salary_details');
    }
};
