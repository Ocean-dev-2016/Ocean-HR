<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $existingColumns = collect(DB::select('SHOW COLUMNS FROM `companies`'))->pluck('Field')->toArray();

        if (!in_array('watermark_logo', $existingColumns, true)) {
            Schema::table('companies', function (Blueprint $table) {
                $table->text('watermark_logo')->nullable()->after('company_favicon');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $existingColumns = collect(DB::select('SHOW COLUMNS FROM `companies`'))->pluck('Field')->toArray();

        if (in_array('watermark_logo', $existingColumns, true)) {
            Schema::table('companies', function (Blueprint $table) {
                $table->dropColumn('watermark_logo');
            });
        }
    }
};
