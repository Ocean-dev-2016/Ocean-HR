<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update provider_type enum to include 'old_crm'
        DB::statement("ALTER TABLE biometric_machines MODIFY COLUMN provider_type ENUM('minop', 'etimeoffice', 'mintra', 'old_crm') DEFAULT 'minop'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum values
        // Note: This will fail if there are records with 'old_crm' provider_type
        DB::statement("ALTER TABLE biometric_machines MODIFY COLUMN provider_type ENUM('minop', 'etimeoffice', 'mintra') DEFAULT 'minop'");
    }
};
