<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('employee_wise_salary_details', function (Blueprint $table) {
            $table->decimal('previous_gross_salary', 12, 2)->nullable()->after('ctc');
        });

        DB::table('employee_wise_salary_details')
            ->whereNull('previous_gross_salary')
            ->update([
                'previous_gross_salary' => DB::raw('ctc'),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_wise_salary_details', function (Blueprint $table) {
            $table->dropColumn('previous_gross_salary');
        });
    }
};
