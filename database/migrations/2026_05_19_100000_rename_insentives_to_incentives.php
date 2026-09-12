<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('insentives')) {
            Schema::rename('insentives', 'incentives');
        }

        if (Schema::hasTable('incentives')) {
            Schema::table('incentives', function (Blueprint $table) {
                if (Schema::hasColumn('incentives', 'insentive_value') && !Schema::hasColumn('incentives', 'incentive_value')) {
                    $table->renameColumn('insentive_value', 'incentive_value');
                }
                if (Schema::hasColumn('incentives', 'insentive_amount') && !Schema::hasColumn('incentives', 'incentive_amount')) {
                    $table->renameColumn('insentive_amount', 'incentive_amount');
                }
            });
        }

        // Update the route_name in sub_menu table
        if (Schema::hasTable('sub_menu')) {
            \DB::table('sub_menu')
                ->where('route_name', 'insentive.index')
                ->update([
                    'name' => 'Incentive',
                    'route_name' => 'incentive.index'
                ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('incentives')) {
            Schema::table('incentives', function (Blueprint $table) {
                if (Schema::hasColumn('incentives', 'incentive_value') && !Schema::hasColumn('incentives', 'insentive_value')) {
                    $table->renameColumn('incentive_value', 'insentive_value');
                }
                if (Schema::hasColumn('incentives', 'incentive_amount') && !Schema::hasColumn('incentives', 'insentive_amount')) {
                    $table->renameColumn('incentive_amount', 'insentive_amount');
                }
            });
            Schema::rename('incentives', 'insentives');
        }

        // Revert route_name in sub_menu table
        if (Schema::hasTable('sub_menu')) {
            \DB::table('sub_menu')
                ->where('route_name', 'incentive.index')
                ->update([
                    'name' => 'Incentive',
                    'route_name' => 'insentive.index'
                ]);
        }
    }
};
