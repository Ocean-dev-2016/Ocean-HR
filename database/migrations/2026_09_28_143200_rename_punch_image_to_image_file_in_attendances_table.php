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
        if (Schema::hasColumn('attendances', 'punch_image') && !Schema::hasColumn('attendances', 'imageFile')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->renameColumn('punch_image', 'imageFile');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('attendances', 'imageFile') && !Schema::hasColumn('attendances', 'punch_image')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->renameColumn('imageFile', 'punch_image');
            });
        }
    }
};
