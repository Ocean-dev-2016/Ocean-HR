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
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->string('company_id');
            $table->string('branch_id', 100)->nullable();
            $table->string('name');
            $table->string('punch_in_minimum');
            $table->string('punch_out');
            $table->string('auto_punch_out');
            
            $table->string('working_hour')->nullable();
            $table->string('breaking_hour')->nullable();
            $table->string('half_day_hour')->nullable();
            $table->string('present_day_hour')->nullable();
            
            $table->string('in_out_grace_period')->nullable();
            $table->string('grace_period')->nullable();
            $table->string('employee_max_working_hours')->nullable();
            $table->string('monitor_by')->nullable();
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
        Schema::dropIfExists('shifts');
    }
};
