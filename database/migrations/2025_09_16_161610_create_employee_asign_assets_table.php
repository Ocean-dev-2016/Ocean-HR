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
        Schema::create('employee_asign_assets', function (Blueprint $table) {
            $table->id();
            $table->string('company_id');
            $table->string('employee_id');
            $table->string('assets_id');
            $table->string('date');
            $table->string('reference_no');
            $table->string('attachment')->nullable();
            $table->string('descrption');
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
        Schema::dropIfExists('employee_asign_assets');
    }
};
