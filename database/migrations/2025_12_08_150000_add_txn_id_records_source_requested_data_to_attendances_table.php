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
            $table->string('txn_id', 255)->nullable()->after('punch_id')->comment('Transaction ID from biometric device');
            $table->string('records_source', 50)->default('api')->after('txn_id')->comment('api, manually, others');
            $table->text('requested_data')->nullable()->after('records_source')->comment('Original request data from biometric device');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['txn_id', 'records_source', 'requested_data']);
        });
    }
};
