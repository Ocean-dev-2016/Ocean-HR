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
    protected $description = 'Automatically punch out employees who forgot to punch out yesterday.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Auto punch-out is disabled per user request.");
        return Command::SUCCESS;

        try {
            $yesterday = Carbon::yesterday()->format('Y-m-d');

            // Find all "In" punches from yesterday that have no corresponding "Out" punch for the same day
            // Actually, per Attendance model, we should find "in" records where there's no "out" record for that employee on that same date.

            $pendingInPunches = Attendance::where('attendance_date', $yesterday)
                ->where('attendace_type', 'in')
                ->whereNotIn('employee_id', function ($query) use ($yesterday) {
                    $query->select('employee_id')
                        ->from('attendances')
                        ->where('attendance_date', $yesterday)
                        ->where('attendace_type', 'out');
                })
                ->get();

            Log::info("AttendanceAutoPunchOut: Found " . $pendingInPunches->count() . " pending punch-outs for " . $yesterday);

            foreach ($pendingInPunches as $record) {
                $shift = Shift::find($record->shift_id);
                
                // Only auto-punch out if the shift has an explicit auto_punch_out time set
                if (!$shift || empty($shift->auto_punch_out) || $shift->auto_punch_out === '00:00:00') {
                    Log::info("AttendanceAutoPunchOut: Skipped Employee #" . $record->employee_id . " - No auto_punch_out time set in shift.");
                    continue;
                }

                $punchOutTime = $shift->auto_punch_out;

                Attendance::create([
                    'company_id' => $record->company_id,
                    'employee_id' => $record->employee_id,
                    'shift_id' => $record->shift_id,
                    'attendance_date' => $yesterday,
                    'create_date' => $yesterday . ' ' . $punchOutTime,
                    'punch_in_time' => $punchOutTime,
                    'attendace_type' => 'out',
                    'remark' => 'System Auto Punch-Out (Day End)',
                    'status' => 'active',
                    'records_source' => 'system',
                    'created_by' => $record->employee_id,
                ]);

                Log::info("AttendanceAutoPunchOut: Employee #" . $record->employee_id . " auto-punched out for " . $yesterday . " at " . $punchOutTime);
            }

            $this->info("Auto punch-out completed for " . $yesterday);

        } catch (\Exception $e) {
            Log::error('AttendanceAutoPunchOut command failed: ' . $e->getMessage());
            $this->error('AttendanceAutoPunchOut command failed: ' . $e->getMessage());
        }
    }
}
