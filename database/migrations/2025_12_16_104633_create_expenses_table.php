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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('company_id');
            $table->string('branch_id')->nullable();
            $table->unsignedBigInteger('team_person_id')->comment('Employee ID who created the expense');
            $table->unsignedBigInteger('expense_category_id');
            $table->unsignedBigInteger('expense_subcategory_id');
            $table->date('date');
            $table->decimal('req_amount', 10, 2)->comment('Requested amount');
            $table->decimal('pass_amount', 10, 2)->nullable()->comment('Approved/Passed amount');
            $table->enum('status', ['pending', 'pass', 'reject'])->default('pending');
            $table->text('reason')->nullable()->comment('Rejection reason');
            $table->string('attachment')->nullable()->comment('File path');
            $table->text('remark')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('company_id');
            $table->index('branch_id');
            $table->index('team_person_id');
            $table->index('expense_category_id');
            $table->index('expense_subcategory_id');
            $table->index('date');
            $table->index('status');
            // $table->foreign('expense_category_id')->references('id')->on('expense_categories')->onDelete('cascade');
            // $table->foreign('expense_subcategory_id')->references('id')->on('expense_sub_categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
