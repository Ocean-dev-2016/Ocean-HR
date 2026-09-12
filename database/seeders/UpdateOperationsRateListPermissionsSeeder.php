<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateOperationsRateListPermissionsSeeder extends Seeder
{
    public function run()
    {
        $subMenu = DB::table('sub_menu')->where('name', 'Operations Rate List')->first();
        if (!$subMenu) {
            echo "SubMenu 'Operations Rate List' not found.\n";
            return;
        }

        $companies = DB::table('companies')->get();
        foreach ($companies as $company) {
            $panelRight = json_decode($company->panel_right, true);
            if (is_array($panelRight)) {
                if (!in_array($subMenu->id, $panelRight)) {
                    $panelRight[] = $subMenu->id;
                    DB::table('companies')->where('id', $company->id)->update([
                        'panel_right' => json_encode($panelRight)
                    ]);
                    echo "Updated permissions for Company ID: {$company->id}\n";
                } else {
                    echo "Company ID: {$company->id} already has permission.\n";
                }
            }
        }
    }
}
