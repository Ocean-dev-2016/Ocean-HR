<?php

namespace Database\Seeders;

use App\Models\MainMenu;
use App\Models\SubMenu;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use function PHPSTORM_META\map;

class MainMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        MainMenu::truncate();
        SubMenu::truncate();
        $data = config('menu_map');

        $main_menu_order_by = 1;
        $sub_menu_order_by = 1;

        foreach ($data as $item) {

            $menu_item['name'] = $item['name'];
            $menu_item['menu_icon'] = isset($item['menu_icon']) ? $item['menu_icon'] : null;
            $menu_item['order_by'] = $main_menu_order_by;
            $menu_item['created_by'] = $item['created_by'];
            $menu_item['updated_by'] = $item['updated_by'];
            $menu_item['status'] = $item['status'];

            $menu = MainMenu::create($menu_item);
            $main_menu_order_by++;

            $sub_menu_order_by = 1;
            foreach ($item['sub_menu'] as $sub_item) {

                $sub_menu_item['name'] = $sub_item['name'];
                $sub_menu_item['route_name'] = isset($sub_item['route_name']) ? $sub_item['route_name'] : null;
                $sub_menu_item['order_by'] = $sub_menu_order_by;
                $sub_menu_item['created_by'] = $sub_item['created_by'];
                $sub_menu_item['updated_by'] = $sub_item['updated_by'];
                $sub_menu_item['main_menu_id'] = $menu->id;
                SubMenu::create($sub_menu_item);
                $sub_menu_order_by++;
            }
        }
    }
}
