<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AttendanceAutoPunchOut extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:auto-punch-out';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically punch out employees based on shift auto punch out time.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $today = Carbon::today()->format('Y-m-d');
            $nowTime = Carbon::now()->format('H:i:s');

            // Find distinct employee & attendance_date pairs with "in" punches that do NOT have an "out" punch
            $pendingInPunches = Attendance::where('attendace_type', 'in')
                ->where('attendance_date', '<=', $today)
                ->whereNotExists(function ($query) {
                    $query->select(\Illuminate\Support\Facades\DB::raw(1))
                        ->from('attendances as out_att')
                        ->whereColumn('out_att.employee_id', 'attendances.employee_id')
                        ->whereColumn('out_att.attendance_date', 'attendances.attendance_date')
                        ->where('out_att.attendace_type', 'out');
                })
                ->select('employee_id', 'attendance_date', \Illuminate\Support\Facades\DB::raw('MAX(company_id) as company_id'), \Illuminate\Support\Facades\DB::raw('MAX(shift_id) as shift_id'))
                ->groupBy('employee_id', 'attendance_date')
                ->get();

            Log::info("AttendanceAutoPunchOut: Found " . $pendingInPunches->count() . " potential pending punch-outs.");

            $autoPunchCount = 0;

            foreach ($pendingInPunches as $record) {
                // Double check if out punch already created in this run
                $alreadyHasOut = Attendance::where('employee_id', $record->employee_id)
                    ->where('attendance_date', $record->attendance_date)
                    ->where('attendace_type', 'out')
                    ->exists();

                if ($alreadyHasOut) {
                    continue;
                }

                $shift = Shift::find($record->shift_id);
                if (!$shift) {
                    $shift = Shift::where('company_id', $record->company_id)->first();
                }

                $punchOutTime = (!empty($shift?->auto_punch_out) && $shift?->auto_punch_out !== '00:00:00')
                    ? $shift->auto_punch_out
                    : (!empty($shift?->punch_out) ? $shift->punch_out : null);

                if (empty($punchOutTime) || $punchOutTime === '00:00:00') {
                    Log::info("AttendanceAutoPunchOut: Skipped Employee #" . $record->employee_id . " for date " . $record->attendance_date . " - No auto_punch_out/punch_out set in shift.");
                    continue;
                }

                // If it's today's record, only auto-punch out if current time is past punchOutTime
                if ($record->attendance_date === $today && $nowTime < $punchOutTime) {
                    continue;
                }

                Attendance::create([
                    'company_id' => $record->company_id,
                    'employee_id' => $record->employee_id,
                    'shift_id' => $shift?->id ?? $record->shift_id,
                    'attendance_date' => $record->attendance_date,
                    'create_date' => $record->attendance_date . ' ' . $punchOutTime,
                    'punch_in_time' => $punchOutTime,
                    'attendace_type' => 'out',
                    'remark' => 'System Auto Punch-Out',
                    'status' => 'active',
                    'records_source' => 'system',
                    'created_by' => $record->employee_id,
                ]);

                $autoPunchCount++;
                Log::info("AttendanceAutoPunchOut: Employee #" . $record->employee_id . " auto-punched out for " . $record->attendance_date . " at " . $punchOutTime);
            }

            $this->info("Auto punch-out completed. Total records processed: " . $autoPunchCount);
            return Command::SUCCESS;

        } catch (\Exception $e) {
            Log::error('AttendanceAutoPunchOut command failed: ' . $e->getMessage());
            $this->error('AttendanceAutoPunchOut command failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
