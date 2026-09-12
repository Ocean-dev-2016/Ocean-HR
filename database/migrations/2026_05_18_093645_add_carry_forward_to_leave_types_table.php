<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_types', function (Blueprint $table) {

            if (!Schema::hasColumn('leave_types', 'carry_forward')) {
                $table->boolean('carry_forward')
                    ->default(0)
                    ->comment('1 = Yes, 0 = No')
                    ->after('mode');
            }

        });
    }

    public function down(): void
    {
        Schema::table('leave_types', function (Blueprint $table) {

            if (Schema::hasColumn('leave_types', 'carry_forward')) {
                $table->dropColumn('carry_forward');
            }

        });
    }
};