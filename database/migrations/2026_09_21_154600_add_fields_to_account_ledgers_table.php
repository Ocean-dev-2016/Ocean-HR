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
        Schema::table('account_ledgers', function (Blueprint $table) {
            if (!Schema::hasColumn('account_ledgers', 'account_head_id')) {
                $table->string('account_head_id')->nullable()->after('company_id');
            }
            if (Schema::hasColumn('account_ledgers', 'employee_id')) {
                $table->string('employee_id')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('account_ledgers', function (Blueprint $table) {
            if (Schema::hasColumn('account_ledgers', 'account_head_id')) {
                $table->dropColumn('account_head_id');
            }
            if (Schema::hasColumn('account_ledgers', 'employee_id')) {
                $table->string('employee_id')->nullable(false)->change();
            }
        });
    }
};
