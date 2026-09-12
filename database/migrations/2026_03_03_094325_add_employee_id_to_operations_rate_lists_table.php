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
        Schema::table('operations_rate_lists', function (Blueprint $table) {
            // For "Lathe Employee wise" operation - store employee instead of product
            $table->unsignedBigInteger('employee_id')->nullable()->after('grade_id');
            // For "Lathe Employee wise" operation - store contract process instead of grade
            $table->unsignedBigInteger('contract_process_id')->nullable()->after('employee_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('operations_rate_lists', function (Blueprint $table) {
            $table->dropColumn(['employee_id', 'contract_process_id']);
        });
    }
};
