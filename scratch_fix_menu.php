<?php

use App\Models\MainMenu;
use App\Models\SubMenu;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// 1. Activate Biometric Manage
$biometricMainMenu = MainMenu::find(10);
if ($biometricMainMenu) {
    $biometricMainMenu->status = 'active';
    $biometricMainMenu->save();
    echo "Activated Biometric Manage (ID 10)\n";
}

// 2. Alternatively, move Biometric Machines to Import Settings (ID 11)
$biometricSubMenu = SubMenu::where('route_name', 'biometric-machines.index')->first();
if ($biometricSubMenu) {
    $biometricSubMenu->main_menu_id = 11; // Move to Import Settings
    $biometricSubMenu->save();
    echo "Moved Biometric Machines to Import Settings (ID 11)\n";
}

// 3. Rename "Import Settings" to "Import & Biometric" or similar? 
// No, user just said it's not showing.

echo "Done\n";
