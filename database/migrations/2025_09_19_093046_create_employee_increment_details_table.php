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
        Schema::create('employee_increment_details', function (Blueprint $table) {
            $table->id();
            $table->string('company_id');
            $table->string('employee_id');
            $table->string('icrement_date');
            $table->string('basic_da');
            $table->string('hra')->nullable();
            $table->string('conveyance_allowance')->nullable();
            $table->string('medical_allowance')->nullable();
            $table->string('special_allowance')->nullable();
            $table->string('pf')->nullable();
            $table->string('effective_month');
            $table->string('effective_year');
            $table->string('designation_id');
            $table->string('per_day_salary')->nullable();
            $table->string('per_hour_salary')->nullable();
            $table->string('remark')->nullable();
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
        Schema::dropIfExists('employee_increment_details');
    }
};
