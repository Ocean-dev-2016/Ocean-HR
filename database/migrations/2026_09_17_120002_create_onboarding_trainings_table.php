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
        Schema::create('onboarding_trainings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('onboarding_id');
            $table->unsignedTinyInteger('module_number')->default(1)->comment('1, 2, 3, etc.');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('pdf_file_path')->nullable();
            $table->string('pdf_file_name')->nullable();
            $table->string('external_url')->nullable();
            $table->string('status')->default('pending')->comment('pending, in_progress, completed');
            $table->dateTime('completed_at')->nullable();
            $table->text('trainer_notes')->nullable();
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
        Schema::dropIfExists('onboarding_trainings');
    }
};
