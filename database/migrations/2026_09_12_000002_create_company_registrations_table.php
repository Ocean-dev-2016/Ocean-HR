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
        if (!Schema::hasTable('company_registrations')) {
            Schema::create('company_registrations', function (Blueprint $table) {
                $table->id();
                $table->string('gst_no')->nullable();
                $table->string('company_name');
                $table->string('person_name');
                $table->string('whatsapp_number');
                $table->string('email');
                $table->string('password');
                $table->string('sp')->nullable();
                $table->unsignedBigInteger('country_id')->nullable();
                $table->unsignedBigInteger('state_id')->nullable();
                $table->unsignedBigInteger('city_id')->nullable();
                $table->unsignedBigInteger('plan_id')->nullable();
                $table->string('date_format')->nullable();
                $table->string('time_format')->nullable();
                $table->double('hra_percentage')->default(40);
                $table->string('otp')->nullable();
                $table->string('status', 50)->default('active');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_registrations');
    }
};
