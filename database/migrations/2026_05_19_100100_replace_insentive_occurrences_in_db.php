<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = array_map('current', \DB::select('SHOW TABLES'));
        foreach ($tables as $table) {
            if ($table === 'migrations') {
                continue;
            }
            try {
                $columns = array_map(function($col) { return $col->Field; }, \DB::select("SHOW COLUMNS FROM `$table`"));
                foreach ($columns as $column) {
                    // Direct replace using SQL statement to avoid chunkById dependencies on 'id' column
                    \DB::statement("UPDATE `$table` SET `$column` = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(`$column`, 'insentive', 'incentive'), 'Insentive', 'Incentive'), 'INSENTIVE', 'INCENTIVE'), 'insentives', 'incentives'), 'Insentives', 'Incentives'), 'INSENTIVES', 'INCENTIVES') WHERE `$column` LIKE '%insentive%'");
                }
            } catch (\Exception $e) {
                // Ignore any table/column errors
            }
        }
    }

    public function down(): void
    {
        // No rollback needed for data normalization
    }
};
