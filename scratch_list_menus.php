<?php

use App\Models\MainMenu;
use App\Models\SubMenu;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$ids = [10, 11];
$menus = MainMenu::with('sub_menu')->whereIn('id', $ids)->get();

foreach ($menus as $menu) {
    echo "Main Menu: " . $menu->name . " (ID: " . $menu->id . ", Status: " . $menu->status . ", Platform: " . $menu->platform . ")\n";
    foreach ($menu->sub_menu as $sub) {
        echo "  - Sub Menu: " . $sub->name . " (ID: " . $sub->id . ", Route: " . $sub->route_name . ", Status: " . $sub->status . ", Platform: " . $sub->platform . ")\n";
    }
}
