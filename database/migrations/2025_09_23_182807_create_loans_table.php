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
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->string('company_id');
            $table->string('employee_id');


            $table->string('loan_type_id'); // Personal, Education, etc.
            $table->decimal('loan_amount', 12, 2);
            $table->decimal('balance_amount', 12, 2)->default(0);
            $table->decimal('emi_amount', 12, 2)->nullable();
            $table->integer('total_installments')->nullable();
            $table->integer('remaining_installments')->nullable();
            $table->enum('interest_type', ['flat', 'reducing'])->default('flat');
            $table->integer('interest_rate')->nullable();

            $table->date('loan_date');
            $table->enum('status', ['pending', 'approved', 'rejected', 'closed'])->default('pending');

            $table->text('remark')->nullable();

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
        Schema::dropIfExists('loans');
    }
};
