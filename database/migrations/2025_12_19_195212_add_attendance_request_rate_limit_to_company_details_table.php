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
        Schema::table('company_details', function (Blueprint $table) {
            if (!Schema::hasColumn('company_details', 'attendance_request_rate_limit_per_minutes')) {
                $table->integer('attendance_request_rate_limit_per_minutes')->nullable()->default(60)->after('company_id')->comment('Maximum number of attendance requests allowed per minute');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_details', function (Blueprint $table) {
            if (Schema::hasColumn('company_details', 'attendance_request_rate_limit_per_minutes')) {
                $table->dropColumn('attendance_request_rate_limit_per_minutes');
            }
        });
    }
};
