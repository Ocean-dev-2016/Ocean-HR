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
            $table->renameColumn('status', 'operation');
            $table->enum('status', ['active', 'inactive'])->default('active')->after('company_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('operations_rate_lists', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->renameColumn('operation', 'status');
        });
    }
};
