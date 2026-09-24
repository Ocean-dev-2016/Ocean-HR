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
        Schema::table('leave_applications', function (Blueprint $table) {
            if (!Schema::hasColumn('leave_applications', 'approval_level')) {
                $table->integer('approval_level')->default(1)->after('status');
            }
            if (!Schema::hasColumn('leave_applications', 'supervisor_status')) {
                $table->string('supervisor_status', 50)->nullable()->after('approval_level');
            }
            if (!Schema::hasColumn('leave_applications', 'supervisor_approved_by')) {
                $table->bigInteger('supervisor_approved_by')->nullable()->after('supervisor_status');
            }
            if (!Schema::hasColumn('leave_applications', 'supervisor_approved_at')) {
                $table->timestamp('supervisor_approved_at')->nullable()->after('supervisor_approved_by');
            }
            if (!Schema::hasColumn('leave_applications', 'supervisor_remark')) {
                $table->text('supervisor_remark')->nullable()->after('supervisor_approved_at');
            }
            if (!Schema::hasColumn('leave_applications', 'hod_status')) {
                $table->string('hod_status', 50)->nullable()->after('supervisor_remark');
            }
            if (!Schema::hasColumn('leave_applications', 'hod_approved_by')) {
                $table->bigInteger('hod_approved_by')->nullable()->after('hod_status');
            }
            if (!Schema::hasColumn('leave_applications', 'hod_approved_at')) {
                $table->timestamp('hod_approved_at')->nullable()->after('hod_approved_by');
            }
            if (!Schema::hasColumn('leave_applications', 'hod_remark')) {
                $table->text('hod_remark')->nullable()->after('hod_approved_at');
            }
            if (!Schema::hasColumn('leave_applications', 'hr_status')) {
                $table->string('hr_status', 50)->nullable()->after('hod_remark');
            }
            if (!Schema::hasColumn('leave_applications', 'hr_approved_by')) {
                $table->bigInteger('hr_approved_by')->nullable()->after('hr_status');
            }
            if (!Schema::hasColumn('leave_applications', 'hr_approved_at')) {
                $table->timestamp('hr_approved_at')->nullable()->after('hr_approved_by');
            }
            if (!Schema::hasColumn('leave_applications', 'hr_remark')) {
                $table->text('hr_remark')->nullable()->after('hr_approved_at');
            }
            if (!Schema::hasColumn('leave_applications', 'rejected_by')) {
                $table->bigInteger('rejected_by')->nullable()->after('hr_remark');
            }
            if (!Schema::hasColumn('leave_applications', 'rejected_by_role')) {
                $table->string('rejected_by_role', 100)->nullable()->after('rejected_by');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_applications', function (Blueprint $table) {
            $cols = [
                'approval_level', 'supervisor_status', 'supervisor_approved_by', 'supervisor_approved_at', 'supervisor_remark',
                'hod_status', 'hod_approved_by', 'hod_approved_at', 'hod_remark',
                'hr_status', 'hr_approved_by', 'hr_approved_at', 'hr_remark',
                'rejected_by', 'rejected_by_role'
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('leave_applications', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
