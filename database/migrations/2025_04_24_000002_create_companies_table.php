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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();

            $table->string('gst_no')->nullable();
            $table->string('company_name');
            $table->string('person_name')->nullable();
            $table->string('whatsapp_number')->nullable();
            $table->string('email')->nullable();
            $table->string('password');
            $table->string('sp');
            $table->string('otp');
            $table->string('pan_card')->nullable();
            $table->text('bank_details')->nullable();

            $table->text('address')->nullable();
            $table->string('country_id', 50)->nullable();
            $table->string('state_id', 50)->nullable();
            $table->string('city_id', 50)->nullable();

            $table->string('plan_id', 50)->comment('current plan id');
            $table->longText('app_right')->nullable();
            $table->longText('panel_right')->nullable();

            $table->date('plan_from')->comment('plan start date')->nullable();
            $table->date('plan_to')->comment('plan expire date')->nullable();
            $table->string('app_key')->comment('plan')->nullable();
            $table->string('panel_url')->nullable();

            $table->string('database_name')->nullable();
            $table->string('database_user')->nullable();
            $table->string('database_password')->nullable();

            $table->integer('max_employee_user_count')->default(0);

            $table->string('mobile_min')->nullable();
            $table->string('mobile_max')->nullable();

            $table->string('branch_type')->default('single')->comment('single. multiple');

            $table->text('company_logo')->nullable();
            $table->text('company_favicon')->nullable();
            $table->text('white_labeling_logo')->nullable();
            $table->text('app_logo')->nullable();
            $table->text('order_header_logo')->nullable();
            $table->text('order_footer_logo')->nullable();
            $table->string('is_copyright_view')->default('yes')->comment('yes, no');

            $table->string('default_password')->nullable();
            $table->string('reset_password')->nullable();
            $table->string('phonecode')->nullable();

            $table->string('status', 50)->default('active')->comment('active, inactive');

            $table->string('created_by', 50)->nullable();
            $table->string('updated_by', 50)->nullable();
            $table->timestamps();
            $table->string('deleted_by', 50)->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
