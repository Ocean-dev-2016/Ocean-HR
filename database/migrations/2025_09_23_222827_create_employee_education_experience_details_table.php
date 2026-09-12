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
        Schema::create('employee_education_experience_details', function (Blueprint $table) {
            $table->id();
            $table->string('company_id');

            $table->string('employee_id');
            $table->string('degree');
            $table->string('institution_name');
            $table->string('month_of_passing_year');
            $table->string('class_or_mark')->nullable();

            $table->string('company_name')->nullable();
            $table->string('joining_date')->nullable();
            $table->string('left_date')->nullable();
            $table->string('designation')->nullable();
            $table->string('ctc_salary')->nullable();

            $table->string('department_name')->nullable();
            $table->string('designation_name')->nullable();
            $table->string('unit_change')->nullable();
            $table->string('effective_date')->nullable();

            $table->string('document_type')->nullable();
            $table->string('document_name')->nullable();
            $table->string('attachment')->nullable();

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
        Schema::dropIfExists('employee_education_experience_details');
    }
};
