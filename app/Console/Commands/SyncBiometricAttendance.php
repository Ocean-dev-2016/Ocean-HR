<?php

namespace App\Console\Commands;

use App\Models\BiometricMachine;
use App\Services\Biometric\BiometricServiceFactory;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncBiometricAttendance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-biometric-attendance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync attendance from biometric machines based on their sync intervals';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting sync biometric attendance...');
        Log::info('Sync biometric attendance started at ' . now());

        try {
            // Get all active pull-based biometric machines
            $machines = BiometricMachine::query()
                ->where('is_active', true)
                ->pullBased() // Only pull-based providers (eTimeOffice, Mintra)
                ->whereNotNull('api_url')
                ->get();

            if ($machines->isEmpty()) {
                $this->info('No active pull-based biometric machines found.');
                Log::info('Sync biometric attendance: No active pull-based machines found');
                return Command::SUCCESS;
            }

            $this->info("Found {$machines->count()} active pull-based machine(s). Checking sync intervals...");

            $syncedCount = 0;
            $skippedCount = 0;
            $failedCount = 0;

            foreach ($machines as $machine) {
                try {
                    // Check if machine should sync based on interval
                    if (!$machine->shouldSync()) {
                        $intervalMinutes = $machine->sync_interval_minutes ?? 60;
                        $nextSyncTime = $machine->last_sync_at 
                            ? $machine->last_sync_at->copy()->addMinutes($intervalMinutes)
                            : now();
                        
                        $this->line("  ⏭️  Machine #{$machine->id} ({$machine->machine_name}) - Skipped. Next sync: " . 
                            ($machine->last_sync_at ? $nextSyncTime->format('Y-m-d H:i:s') : 'Now'));
                        Log::info("Sync biometric attendance: Machine #{$machine->id} skipped - interval not elapsed", [
                            'machine_id' => $machine->id,
                            'machine_name' => $machine->machine_name,
                            'last_sync_at' => $machine->last_sync_at,
                            'sync_interval_minutes' => $intervalMinutes,
                            'next_sync_time' => $nextSyncTime,
                        ]);
                        $skippedCount++;
                        continue;
                    }

                    // Check if machine has required credentials
                    if (!$machine->shouldShowSyncButton()) {
                        $this->warn("  ⚠️  Machine #{$machine->id} ({$machine->machine_name}) - Missing credentials. Skipping.");
                        Log::warning("Sync biometric attendance: Machine #{$machine->id} missing credentials", [
                            'machine_id' => $machine->id,
                            'machine_name' => $machine->machine_name,
                        ]);
                        $skippedCount++;
                        continue;
                    }

                    $this->info("  🔄 Syncing Machine #{$machine->id} ({$machine->machine_name})...");
                    Log::info("Sync biometric attendance: Starting sync for machine #{$machine->id}", [
                        'machine_id' => $machine->id,
                        'machine_name' => $machine->machine_name,
                        'company_id' => $machine->company_id,
                        'provider_type' => $machine->provider_type,
                    ]);

                    // Update sync status to pending
                    $machine->update([
                        'last_sync_status' => 'pending',
                        'last_sync_error' => null,
                    ]);

                    // Get service and fetch attendance
                    $service = BiometricServiceFactory::make($machine->provider_type, $machine);
                    
                    // Fetch attendance for last 7 days or since last sync
                    $endDate = Carbon::now();
                    $startDate = $machine->last_sync_at 
                        ? Carbon::parse($machine->last_sync_at)->subDay() // Start from day before last sync to avoid gaps
                        : Carbon::now()->subDays(7); // Default to last 7 days

                    $this->line("    Date range: {$startDate->format('Y-m-d')} to {$endDate->format('Y-m-d')}");

                    // Fetch attendance records
                    $attendanceRecords = $service->fetchAttendance($startDate, $endDate);

                    // Extract response data
                    $recordsFetched = $attendanceRecords['records_fetched'] ?? 0;
                    $recordsTransformed = $attendanceRecords['records_transformed'] ?? 0;
                    $recordsStored = $attendanceRecords['records_stored'] ?? 0;

                    // Update machine sync status to success
                    $machine->update([
                        'last_sync_at' => now(),
                        'last_sync_status' => 'success',
                        'last_sync_error' => null,
                    ]);

                    $this->info("    ✅ Success! Fetched: {$recordsFetched}, Transformed: {$recordsTransformed}, Stored: {$recordsStored}");
                    Log::info("Sync biometric attendance: Machine #{$machine->id} sync completed successfully", [
                        'machine_id' => $machine->id,
                        'records_fetched' => $recordsFetched,
                        'records_transformed' => $recordsTransformed,
                        'records_stored' => $recordsStored,
                    ]);

                    $syncedCount++;

                } catch (\Exception $e) {
                    // Update machine sync status to failed
                    $machine->update([
                        'last_sync_status' => 'failed',
                        'last_sync_error' => $e->getMessage(),
                    ]);

                    $this->error("    ❌ Failed! Error: {$e->getMessage()}");
                    Log::error("Sync biometric attendance: Machine #{$machine->id} sync failed", [
                        'machine_id' => $machine->id,
                        'machine_name' => $machine->machine_name,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);

                    $failedCount++;
                }
            }

            // Summary
            $this->newLine();
            $this->info("📊 Sync Summary:");
            $this->line("  ✅ Synced: {$syncedCount}");
            $this->line("  ⏭️  Skipped: {$skippedCount}");
            $this->line("  ❌ Failed: {$failedCount}");
            $this->line("  📋 Total: {$machines->count()}");

            Log::info('Sync biometric attendance completed', [
                'synced' => $syncedCount,
                'skipped' => $skippedCount,
                'failed' => $failedCount,
                'total' => $machines->count(),
            ]);

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Fatal error: {$e->getMessage()}");
            Log::error('Sync biometric attendance fatal error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return Command::FAILURE;
        }
    }
}
