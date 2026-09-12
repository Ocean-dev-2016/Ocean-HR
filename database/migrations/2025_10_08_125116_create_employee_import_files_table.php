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
        Schema::create('employee_import_files', function (Blueprint $table) {
            $table->id();
            $table->string('company_id');
            $table->string('filename')->nullable();
            $table->integer('total_employees')->default(0);
            $table->integer('total_success_employees')->default(0);
            $table->integer('total_failed_employees')->default(0);
            $table->longText('errors')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
         
            $table->string('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_import_files');
    }
};
