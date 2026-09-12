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
        // Check if column exists to avoid duplicate error
        if (!Schema::hasColumn('employee_wise_salary_details', 'welfare_fund_amount')) {
            Schema::table('employee_wise_salary_details', function (Blueprint $table) {
                $table->decimal('welfare_fund_amount', 10, 2)->nullable()->default(0)->after('is_welfare_fund_applied');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_wise_salary_details', function (Blueprint $table) {
            $table->dropColumn('welfare_fund_amount');
        });
    }
};
