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
        Schema::create('application_version', function (Blueprint $table) {
            $table->id();
            $table->string('version');
            $table->string('apk_file')->nullable(); // store file path
            $table->tinyInteger('is_force_update')->default(0);
            $table->text('update_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_version');
    }
};
