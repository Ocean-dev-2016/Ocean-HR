<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('operations_rate_lists', function (Blueprint $table) {
            for ($i = 1; $i <= 31; $i++) {
                $table->decimal("day_{$i}_ot", 12, 2)->nullable()->after("day_{$i}_r");
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
                $table->dropColumn("day_{$i}_ot");
            }
        });
    }
};
