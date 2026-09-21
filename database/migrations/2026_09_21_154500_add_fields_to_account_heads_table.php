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
        Schema::table('account_heads', function (Blueprint $table) {
            if (Schema::hasColumn('account_heads', 'company_id')) {
                $table->string('company_id')->nullable()->change();
            } else {
                $table->string('company_id')->nullable()->after('id');
            }

            if (!Schema::hasColumn('account_heads', 'name')) {
                $table->string('name')->nullable()->after('company_id');
            }

            if (!Schema::hasColumn('account_heads', 'account_head_id')) {
                $table->string('account_head_id')->nullable()->after('name');
            }

            if (!Schema::hasColumn('account_heads', 'credit_amount')) {
                $table->string('credit_amount')->nullable()->after('debit_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('account_heads', function (Blueprint $table) {
            $columnsToDrop = [];
            if (Schema::hasColumn('account_heads', 'credit_amount')) {
                $columnsToDrop[] = 'credit_amount';
            }
            if (Schema::hasColumn('account_heads', 'account_head_id')) {
                $columnsToDrop[] = 'account_head_id';
            }
            if (Schema::hasColumn('account_heads', 'name')) {
                $columnsToDrop[] = 'name';
            }
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
