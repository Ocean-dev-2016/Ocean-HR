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
        Schema::create('loan_repayments', function (Blueprint $table) {
            $table->id();
            $table->string('loan_id');
            $table->decimal('loan_amount', 12, 2);        // original principal per installment
            $table->decimal('interest', 12, 2);           // interest portion
            $table->decimal('installment_amount', 12, 2); // loan_amount + interest
            $table->date('due_date');                     // when EMI is due
            $table->datetime('paid_date')->nullable();        // when EMI was actually paid
            $table->enum('status', ['pending', 'paid'])->default('pending');

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
        Schema::dropIfExists('loan_repayments');
    }
};
