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
        Schema::table('leave_applications', function (Blueprint $table) {
            // Check if employee_id column doesn't exist, then add it
            if (!Schema::hasColumn('leave_applications', 'employee_id')) {
                // Check if branch_id exists to determine position
                if (Schema::hasColumn('leave_applications', 'branch_id')) {
                    $table->string('employee_id')->nullable()->after('branch_id');
                } else {
                    // If branch_id doesn't exist, add after company_id
                    $table->string('employee_id')->nullable()->after('company_id');
                }
            } else {
                // If column exists but is nullable, ensure it's properly defined
                // This is a safety check - won't modify if already exists
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_applications', function (Blueprint $table) {
            // Only drop if column exists
            if (Schema::hasColumn('leave_applications', 'employee_id')) {
                $table->dropColumn('employee_id');
            }
        });
    }
};
