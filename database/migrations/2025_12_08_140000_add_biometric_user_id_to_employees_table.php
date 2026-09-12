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
        Schema::table('employees', function (Blueprint $table) {
            $table->string('biometric_user_id', 50)->nullable()->after('employee_code');
        });

        // Add unique index for company_id + branch_id + biometric_user_id combination
        // MySQL allows multiple NULLs in unique constraints, so this will work correctly:
        // - Multiple rows can have NULL biometric_user_id
        // - Non-NULL biometric_user_id values must be unique per company_id + branch_id
        // Schema::table('employees', function (Blueprint $table) {
        //     $table->unique(['company_id', 'branch_id', 'biometric_user_id'], 'unique_biometric_user_per_company_branch');
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // $table->dropUnique('unique_biometric_user_per_company_branch');
            $table->dropColumn('biometric_user_id');
        });
    }
};
