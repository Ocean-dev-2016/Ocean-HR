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
        Schema::create('expense_sub_categories', function (Blueprint $table) {
            $table->id();
            $table->string('company_id');
            $table->string('branch_id')->nullable();
            $table->unsignedBigInteger('expense_category_id');
            $table->string('name');
            $table->enum('expense_type', ['General', 'KM', 'Food'])->default('General');
            $table->text('team_person_ids')->nullable()->comment('Comma separated employee IDs');
            $table->decimal('min_amount', 10, 2)->nullable()->comment('For General type');
            $table->decimal('max_amount', 10, 2)->nullable()->comment('For General type');
            $table->decimal('per_km_rate', 10, 2)->nullable()->comment('For KM type');
            $table->decimal('fix_amount', 10, 2)->nullable()->comment('For Food type');
            $table->time('from_time')->nullable()->comment('For Food type');
            $table->time('to_time')->nullable()->comment('For Food type');
            $table->tinyInteger('is_image_required')->nullable()->default(0)->comment('0=No, 1=Yes');
            $table->string('status')->default('active')->comment('active, inactive');
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('company_id');
            $table->index('branch_id');
            $table->index('expense_category_id');
            $table->index('expense_type');
            $table->index('status');
            // $table->foreign('expense_category_id')->references('id')->on('expense_categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense_sub_categories');
    }
};
