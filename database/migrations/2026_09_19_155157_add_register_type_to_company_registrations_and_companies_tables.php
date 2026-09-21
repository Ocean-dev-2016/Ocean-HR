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
        if (Schema::hasTable('company_registrations')) {
            Schema::table('company_registrations', function (Blueprint $table) {
                if (!Schema::hasColumn('company_registrations', 'register_type')) {
                    $table->string('register_type', 50)->default('manual')->after('status');
                }
            });
        }

        if (Schema::hasTable('companies')) {
            Schema::table('companies', function (Blueprint $table) {
                if (!Schema::hasColumn('companies', 'register_type')) {
                    $table->string('register_type', 50)->default('manual')->after('status');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('company_registrations')) {
            Schema::table('company_registrations', function (Blueprint $table) {
                if (Schema::hasColumn('company_registrations', 'register_type')) {
                    $table->dropColumn('register_type');
                }
            });
        }

        if (Schema::hasTable('companies')) {
            Schema::table('companies', function (Blueprint $table) {
                if (Schema::hasColumn('companies', 'register_type')) {
                    $table->dropColumn('register_type');
                }
            });
        }
    }
};
