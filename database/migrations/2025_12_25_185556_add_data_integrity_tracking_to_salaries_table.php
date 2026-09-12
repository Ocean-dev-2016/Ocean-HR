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
        Schema::table('salaries', function (Blueprint $table) {
            // Data integrity tracking fields
            $table->timestamp('calculation_date')->nullable()->after('added_date')->comment('When salary was calculated');
            $table->timestamp('attendance_snapshot_date')->nullable()->after('calculation_date')->comment('Last attendance record timestamp at calculation time');
            $table->integer('attendance_records_count')->nullable()->after('attendance_snapshot_date')->comment('Number of attendance records used in calculation');
            $table->timestamp('leave_snapshot_date')->nullable()->after('attendance_records_count')->comment('Last leave record timestamp at calculation time');
            $table->integer('leave_records_count')->nullable()->after('leave_snapshot_date')->comment('Number of leave records used in calculation');
            $table->timestamp('holiday_snapshot_date')->nullable()->after('leave_records_count')->comment('Last holiday record timestamp at calculation time');
            $table->integer('holiday_records_count')->nullable()->after('holiday_snapshot_date')->comment('Number of holiday records used in calculation');
            $table->timestamp('salary_detail_snapshot_date')->nullable()->after('holiday_records_count')->comment('Salary detail last updated timestamp at calculation time');
            $table->boolean('data_modified')->default(false)->after('salary_detail_snapshot_date')->comment('Flag to indicate if source data has been modified');
            $table->text('data_modification_details')->nullable()->after('data_modified')->comment('JSON details of what was modified');
            $table->boolean('is_locked')->default(false)->after('data_modification_details')->comment('Lock salary to prevent recalculation');
            $table->timestamp('locked_at')->nullable()->after('is_locked')->comment('When salary was locked');
            $table->string('locked_by')->nullable()->after('locked_at')->comment('User who locked the salary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salaries', function (Blueprint $table) {
            $table->dropColumn([
                'calculation_date',
                'attendance_snapshot_date',
                'attendance_records_count',
                'leave_snapshot_date',
                'leave_records_count',
                'holiday_snapshot_date',
                'holiday_records_count',
                'salary_detail_snapshot_date',
                'data_modified',
                'data_modification_details',
                'is_locked',
                'locked_at',
                'locked_by'
            ]);
        });
    }
};
