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
        Schema::table('companies', function (Blueprint $table) {
            if (!Schema::hasColumn('companies', 'import_attendance_format')) {
                $table->string('import_attendance_format', 50)->default('standard')->after('employee_code_auto_generation')->comment('standard, custom');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (Schema::hasColumn('companies', 'import_attendance_format')) {
                $table->dropColumn('import_attendance_format');
            }
        });
    }
};
