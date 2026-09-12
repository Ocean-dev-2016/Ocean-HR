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
        Schema::create('operations_rate_lists', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->string('status'); // CLEANING, Lathe Employee wise, etc.
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('grade_id');
            $table->integer('month');
            $table->integer('year');
            
            for ($i = 1; $i <= 31; $i++) {
                $table->decimal('day_' . $i, 10, 2)->default(0);
            }
            
            $table->decimal('total_qty', 10, 2)->default(0);
            $table->decimal('rate', 10, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operations_rate_lists');
    }
};
