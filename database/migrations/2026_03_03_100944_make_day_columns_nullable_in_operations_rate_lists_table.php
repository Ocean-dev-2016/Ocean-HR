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
            for ($i = 1; $i <= 31; $i++) {
                $table->decimal('day_' . $i, 10, 2)->nullable()->default(null)->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('operations_rate_lists', function (Blueprint $table) {
            for ($i = 1; $i <= 31; $i++) {
                $table->decimal('day_' . $i, 10, 2)->nullable(false)->default(0)->change();
            }
        });
    }
};
