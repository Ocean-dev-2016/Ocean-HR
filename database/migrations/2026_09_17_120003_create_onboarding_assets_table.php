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
        Schema::create('onboarding_assets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('onboarding_id');
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->unsignedBigInteger('assets_allocation_master_id')->nullable();
            $table->string('asset_type')->comment('mobile, sim, laptop, desktop, tshirt, shoes, id_card, other');
            $table->string('asset_name');
            $table->string('asset_code_or_serial')->nullable();
            $table->string('specification_or_size')->nullable()->comment('e.g., T-Shirt Size M/L/XL, Shoe Size 8/9/10, Laptop RAM 16GB');
            $table->date('issued_date')->nullable();
            $table->string('status')->default('pending')->comment('pending, assigned, handed_over');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('onboarding_id')->references('id')->on('onboardings')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('onboarding_assets');
    }
};
