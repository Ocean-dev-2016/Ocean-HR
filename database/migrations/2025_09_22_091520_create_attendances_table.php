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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->string('company_id');
            $table->string('employee_id');
            $table->string('shift_id');
            $table->date('attendance_date');
            // $table->date('create_date');
            $table->date('create_date')->useCurrent();

            $table->time('punch_in_time')->nullable();
            $table->time('punch_out_time')->nullable();
            $table->string('attendace_type');
            $table->string('remark')->nullable();
            $table->string('status')->default('active')->comment('active, inactive')->nullable();
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
        Schema::dropIfExists('attendances');
    }
};
