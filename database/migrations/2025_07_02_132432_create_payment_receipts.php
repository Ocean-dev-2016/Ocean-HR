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
        Schema::create('payment_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('company_id');
            $table->string('branch_id')->nullable();
            $table->string('department');
            $table->string('employee_id');
            $table->string('effect_on_month')->nullable();
            $table->string('effect_of_year')->nullable();
            $table->date('date');
            $table->string('amount');
            $table->string('account_head_id');
            $table->string('payment_mode')->nullable();

            $table->string('payment_type')->nullable();
            $table->string('upi_no')->nullable();
            $table->string('cheque_no')->nullable();
            $table->string('receipt_no');
            $table->string('remark')->nullable();
            $table->string('status')->default('pending')->comment('pending, approve');
            $table->string('deleted_by')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_receipts');
    }
};
