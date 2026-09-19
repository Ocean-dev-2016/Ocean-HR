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
        Schema::create('onboardings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->string('onboarding_code')->unique()->nullable();
            
            // Candidate / Employee basic info
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('father_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('full_name')->nullable();
            $table->string('email')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('other_number')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender')->nullable();
            $table->string('blood_group')->nullable();
            $table->string('marital_status')->nullable();
            $table->text('current_address')->nullable();
            $table->text('permanent_address')->nullable();

            // Employment setup
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('sub_department_id')->nullable();
            $table->unsignedBigInteger('designation_id')->nullable();
            $table->unsignedBigInteger('role_id')->nullable();
            $table->unsignedBigInteger('shift_id')->nullable();
            $table->unsignedBigInteger('reporting_manager_id')->nullable();
            $table->unsignedBigInteger('buddy_id')->nullable();
            $table->date('joining_date')->nullable();
            $table->unsignedSmallInteger('probation_period_months')->nullable()->default(3)->comment('Probation duration in months, e.g. 1, 3, 6');
            $table->date('probation_end_date')->nullable()->comment('Expected confirmation date');
            $table->string('probation_status')->default('on_probation')->comment('on_probation, confirmed, extended, waived');
            $table->text('probation_notes')->nullable();
            $table->string('employment_type')->nullable(); // full_time, part_time, probation, intern, contract

            // Job Description & KRA/KPI
            $table->text('job_description')->nullable()->comment('Detailed role responsibilities and duties');
            $table->longText('kra_kpi_details')->nullable()->comment('JSON encoded KRA & KPI metrics and weightages');
            $table->unsignedTinyInteger('attendance_target_percentage')->default(95)->comment('Monthly attendance expectation in %');
            $table->unsignedTinyInteger('late_mark_tolerance')->default(2)->comment('Allowed grace late marks per month');
            $table->decimal('daily_working_hours_target', 4, 2)->default(8.50)->comment('Daily work hours target');
            $table->boolean('jd_acknowledged')->default(false);
            $table->dateTime('jd_acknowledged_at')->nullable();

            // Workflow status & progress
            $table->string('status')->default('in_progress')->comment('draft, in_progress, completed, cancelled');
            $table->unsignedTinyInteger('current_step')->default(1)->comment('1 to 6');
            $table->unsignedTinyInteger('progress_percentage')->default(15);
            
            // Step 3 Company Overview
            $table->boolean('company_overview_acknowledged')->default(false);
            $table->dateTime('company_overview_acknowledged_at')->nullable();
            $table->text('company_overview_notes')->nullable();

            // Notes & completion
            $table->text('remarks')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->unsignedBigInteger('completed_by')->nullable();

            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('onboardings');
    }
};
