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
        Schema::create('attendance_import_files', function (Blueprint $table) {
            $table->id();
            $table->string('company_id')->nullable();
            $table->string('filename')->nullable();
            $table->string('file_type')->nullable()->comment('excel, pdf');
            $table->string('status')->default('pending')->comment('pending, processing, completed, failed');
            $table->integer('total_rows')->default(0);
            $table->integer('total_success')->default(0);
            $table->integer('total_failed')->default(0);
            $table->integer('total_duplicates')->default(0);
            $table->text('errors')->nullable();
            $table->text('processing_log')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_import_files');
    }
};

