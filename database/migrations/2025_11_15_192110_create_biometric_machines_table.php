<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('biometric_machines', function (Blueprint $table) {
            $table->id();
            $table->string('company_id');

            // Provider type: minop (push-based), etimeoffice (pull-based), mintra (pull-based)
            $table->enum('provider_type', ['minop', 'etimeoffice', 'mintra'])->default('minop');

            $table->string('serial_number')->nullable()->index();
            $table->string('machine_name')->nullable();
            $table->string('ip_address');
            $table->string('port');

            // API credentials for pull-based providers (eTimeOffice, Mintra)
            $table->string('api_url')->nullable();
            $table->enum('auth_type', ['basic', 'bearer_token', 'api_key', 'custom'])->default('basic');
            $table->string('corporate_id')->nullable(); // For eTimeOffice
            $table->string('api_username')->nullable();
            $table->text('api_password')->nullable(); // Encrypted, so using text
            $table->text('bearer_token')->nullable(); // For Bearer Token auth
            $table->string('api_key_name')->nullable(); // For API Key auth
            $table->text('api_key_value')->nullable(); // For API Key auth (encrypted)
            $table->text('custom_headers')->nullable(); // JSON for custom headers

            $table->string('status')->default('active')->comment('active, inactive');

            // Active machine flag - only one machine per company can be active
            $table->boolean('is_active')->default(false);

            // Sync tracking fields
            $table->timestamp('last_sync_at')->nullable();
            $table->enum('last_sync_status', ['success', 'failed', 'pending'])->nullable();
            $table->text('last_sync_error')->nullable();
            $table->integer('sync_interval_minutes')->default(60);

            $table->text('description')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps();
            $table->string('deleted_by')->nullable();
            $table->softDeletes();

            // Indexes
            $table->index(['company_id', 'ip_address', 'port']);
            $table->index(['provider_type', 'company_id']);
            $table->index(['company_id', 'is_active']);

            // Note: Unique constraint for single active machine per company will be handled at application level
            // MySQL doesn't support unique constraint with condition (WHERE is_active = true) easily
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('biometric_machines');
    }
};
