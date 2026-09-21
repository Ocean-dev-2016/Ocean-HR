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
            $table->string('company_id')->nullable()->change();
            $table->string('name')->nullable()->after('company_id');
            $table->string('account_head_id')->nullable()->after('name');
            $table->string('credit_amount')->nullable()->after('debit_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('account_heads', function (Blueprint $table) {
            $table->dropColumn(['name', 'account_head_id', 'credit_amount']);
            $table->string('company_id')->nullable(false)->change();
        });
    }
};
