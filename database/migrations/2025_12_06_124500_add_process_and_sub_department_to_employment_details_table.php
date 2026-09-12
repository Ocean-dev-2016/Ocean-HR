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
        Schema::table('employment_details', function (Blueprint $table) {
            if (!Schema::hasColumn('employment_details', 'sub_department_id')) {
                $table->unsignedBigInteger('sub_department_id')->nullable()->after('department_id');
            }

            if (!Schema::hasColumn('employment_details', 'process_id')) {
                $table->unsignedBigInteger('process_id')->nullable()->after('sub_department_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employment_details', function (Blueprint $table) {
            if (Schema::hasColumn('employment_details', 'process_id')) {
                $table->dropColumn('process_id');
            }

            if (Schema::hasColumn('employment_details', 'sub_department_id')) {
                $table->dropColumn('sub_department_id');
            }
        });
    }
};
