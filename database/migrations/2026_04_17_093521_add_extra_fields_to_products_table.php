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
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'operation')) {
                $table->string('operation')->nullable()->after('rate');
            }
            if (!Schema::hasColumn('products', 'process')) {
                $table->string('process')->nullable()->after('operation');
            }
            if (!Schema::hasColumn('products', 'ot_text')) {
                $table->string('ot_text')->nullable()->after('process');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['operation', 'process', 'ot_text']);
        });
    }
};
