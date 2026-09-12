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
        Schema::table('attendances', function (Blueprint $table) {
            $table->string('punch_id')->nullable()->index()->after('attendace_type');
            $table->string('device_serial')->nullable()->after('punch_id');
            $table->string('device_ip')->nullable()->after('device_serial');
        });

        Schema::table('biometric_machines', function (Blueprint $table) {
            $table->string('serial_number')->nullable()->index()->after('company_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['punch_id', 'device_serial', 'device_ip']);
        });

        Schema::table('biometric_machines', function (Blueprint $table) {
            $table->dropColumn(['serial_number']);
        });
    }
};
