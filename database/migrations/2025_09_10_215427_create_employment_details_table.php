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
        Schema::create('employment_details', function (Blueprint $table) {
            $table->id();
            $table->string('company_id');
            $table->string('employee_id');
            $table->string('designation_type')->nullable();
            $table->string('designation_id')->nullable();

            $table->string('department_id')->nullable();
            $table->string('sub_department_id')->nullable();
            $table->string('process_id')->nullable();
            $table->string('date_of_joining')->nullable();
            $table->string('confirmation_date')->nullable();
            $table->string('employee_pf_no')->nullable();
            $table->string('uan_no')->nullable();
            $table->string('payment_mode')->nullable();
            $table->string('employment_type')->nullable();
            $table->string('shift')->nullable();
            $table->string('outdoor_attendance')->nullable();
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
        Schema::dropIfExists('employment_details');
    }
};
