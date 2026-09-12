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
            if (!Schema::hasColumn('companies', 'date_format')) {
                $table->string('date_format', 50)->default('d/m/Y')->after('branch_type');
            }

            if (!Schema::hasColumn('companies', 'time_format')) {
                $table->string('time_format', 50)->default('H:i')->after('date_format');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (Schema::hasColumn('companies', 'time_format')) {
                $table->dropColumn('time_format');
            }

            if (Schema::hasColumn('companies', 'date_format')) {
                $table->dropColumn('date_format');
            }
        });
    }
};
