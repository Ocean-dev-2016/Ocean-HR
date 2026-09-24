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
        if (!Schema::hasColumn('attendances', 'punch_image')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->text('punch_image')->nullable()->after('punch_in_time');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('attendances', 'punch_image')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->dropColumn('punch_image');
            });
        }
    }
};
