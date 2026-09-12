<?php

namespace Database\Seeders;

use App\Models\PlanMaster;
use App\Models\SubMenu;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlanMasterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (PlanMaster::count()) {
            PlanMaster::truncate();
        }

        $panel_right_ids = null;
        $panelSubMenu = SubMenu::where('platform', 'panel')->get();

        if (count($panelSubMenu) > 0) {
            $panel_right_ids = $panelSubMenu->pluck('id')->join(",");
        }

        $data = [];
        $data[] = [
            'name' => 'Free Plan',
            'plan_valid_day' => 7,
            'max_employee_user_count' => 5,
            'plan_type' => 'general',
            'panel_right' => $panel_right_ids,
            'status' => 'active',
            'created_by' => 0,
            'updated_by' => 0
        ];

        $data[] = [
            'name' => '30 Day',
            'plan_valid_day' => 30,
            'max_employee_user_count' => 5,
            'plan_type' => 'general',
            'panel_right' => $panel_right_ids,
            'status' => 'active',
            'created_by' => 0,
            'updated_by' => 0,
        ];

        $data[] = [
            'name' => '356 Day',
            'plan_valid_day' => 365,
            'max_employee_user_count' => 5,
            'plan_type' => 'general',
            'panel_right' => $panel_right_ids,
            'status' => 'active',
            'created_by' => 0,
            'updated_by' => 0,
        ];

        foreach ($data as $record) {
            PlanMaster::create($record);
        }
        $this->command->info("Plan master Done.");
    }
}
