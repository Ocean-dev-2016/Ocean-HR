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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('company_id');
            $table->string('branch_id')->nullable();
            $table->string('parent_id')->nullable();
            $table->string('employee_code')->nullable();

            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('father_name')->nullable();
            $table->string('full_name');

            $table->string('username');
            $table->string('password');
            $table->string('sp');
            $table->string('role_id')->nullable();

            $table->string('email')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('other_number')->nullable();

            $table->string('date_of_birth')->nullable();
            $table->string('gender',10)->comment("male,female")->nullable();
            $table->string('country_id')->nullable();
            $table->string('state_id')->nullable();
            $table->string('city_id')->nullable();
            $table->string('current_address')->nullable();
            $table->string('permanent_address')->nullable();


            $table->string('aadhar_card_number', 20)->nullable();
            $table->string('pan_card_number', 12)->nullable();
            $table->string('marital_status', 50)->comment("single,married")->nullable();
            $table->string('date_of_anniversary')->nullable();
            $table->string('code')->nullable();
            $table->string('grade')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('ifsc_code')->nullable();

            $table->string('app_version')->nullable();
            $table->text('device_token')->nullable();
            $table->text('device_token_app')->nullable();

            $table->string('status')->default('active')->comment('active, inactive');
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps();
            $table->string('deleted_by')->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
