# Biometric Attendance Auto-Sync Cron Setup

## Overview
This document explains how the automatic biometric attendance sync works based on individual machine sync intervals.

## Implementation

### 1. Command Created
**File:** `app/Console/Commands/SyncBiometricAttendance.php`

**Command:** `biometric:sync-attendance`

**Description:** Auto-syncs attendance from biometric machines based on their configured sync intervals.

### 2. Scheduler Configuration
**File:** `app/Console/Kernel.php`

The command is scheduled to run **every minute** to check all active biometric machines and sync those whose interval has elapsed.

```php
$schedule->command('biometric:sync-attendance')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/biometric-sync-' . now()->format('Y-m-d') . '.log'));
```

**Note:** Log files are created daily with format: `biometric-sync-YYYY-MM-DD.log` (e.g., `biometric-sync-2026-01-16.log`)

### 3. How It Works

#### Process Flow:
1. **Get Active Machines**: Fetches all active pull-based biometric machines (eTimeOffice, Mintra only)
2. **Check Sync Interval**: For each machine, checks if sync interval has elapsed using `shouldSync()` method
3. **Sync Required Machines**: Only syncs machines where:
   - Machine is active (`is_active = true`)
   - Machine is pull-based provider (eTimeOffice or Mintra)
   - Sync interval has elapsed (based on `sync_interval_minutes` and `last_sync_at`)
   - Machine has required credentials configured
4. **Update Status**: Updates `last_sync_at`, `last_sync_status`, and `last_sync_error` fields

#### Sync Interval Logic:
- If `last_sync_at` is NULL: Machine is synced immediately
- If `last_sync_at` exists: Machine is synced if `now() >= last_sync_at + sync_interval_minutes`
- Default interval: 60 minutes (if `sync_interval_minutes` is not set)

#### Date Range for Fetching:
- If `last_sync_at` exists: Fetch from day before last sync to today (to avoid gaps)
- If never synced: Fetch last 7 days

### 4. Cron Setup Methods

There are two ways to run the Laravel scheduler:

#### Method 1: Web Route (Recommended for this setup)

**Route:** `GET /run-cron`

**File:** `routes/web.php`

The scheduler is triggered via a web route that calls `Artisan::call('schedule:run')`. This method is useful when:
- You don't have direct server cron access
- You want to trigger cron via external cron service (e.g., EasyCron, cron-job.org)
- You need to monitor cron execution via web requests

**Setup Steps:**

1. **External Cron Service Setup:**
   - Use a service like EasyCron, cron-job.org, or similar
   - Set up a cron job that makes an HTTP GET request to: `https://yourdomain.com/run-cron`
   - Configure it to run **every minute** (cron expression: `* * * * *`)

2. **Verify Route is Working:**
   ```bash
   # Test via browser or curl
   curl https://yourdomain.com/run-cron
   ```

3. **Monitor Execution:**
   - Check Laravel logs: `storage/logs/laravel.log`
   - Look for: `run-cron on web request`
   - Check biometric sync logs: `storage/logs/biometric-sync-YYYY-MM-DD.log`

**Route Code:**
```php
Route::get('/run-cron', function () {
    \Log::info('run-cron on web request ' . now());
    Artisan::call('schedule:run');
    return "Run cron command";
});
```

#### Method 2: Server Cron (Traditional Method)

**Step 1: Add Laravel Scheduler to Server Cron**
Add this line to your server's crontab (run `crontab -e`):

```bash
* * * * * cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1
```

**Important:** Replace `/path/to/your/project` with your actual Laravel project path.

**Step 2: Verify Cron is Running**
Test the scheduler manually:
```bash
php artisan schedule:run
```

Or test the biometric sync command directly:
```bash
php artisan biometric:sync-attendance
```

**Step 3: Check Logs**
Monitor the sync logs:
```bash
tail -f storage/logs/biometric-sync-YYYY-MM-DD.log
```

Also check Laravel logs:
```bash
tail -f storage/logs/laravel.log | grep "Biometric sync"
```

### 5. Log Files

The biometric sync command logs to daily log files:
- **Path:** `storage/logs/biometric-sync-YYYY-MM-DD.log`
- **Format:** Daily log files (e.g., `biometric-sync-2026-01-16.log`)
- **Content:** Command output, sync status, errors, and summaries

**View Today's Log:**
```bash
tail -f storage/logs/biometric-sync-$(date +%Y-%m-%d).log
```

**View Specific Date:**
```bash
tail -f storage/logs/biometric-sync-2026-01-16.log
```

### 6. Machine Configuration

#### Sync Interval Setting:
Each biometric machine can be configured with its own sync interval:

- **Field:** `sync_interval_minutes` (in `biometric_machines` table)
- **Default:** 60 minutes
- **Location:** Biometric Machine form → "Sync Interval (Minutes)" field
- **Example:** Setting to `5` means the machine will sync every 5 minutes

#### Machine Status:
- Machine must be **active** (`is_active = true`)
- Must be **pull-based provider** (eTimeOffice or Mintra)
- Must have **API credentials** configured based on auth type

### 7. Features

✅ **Per-Machine Intervals**: Each machine syncs at its own configured interval
✅ **Automatic Scheduling**: No manual intervention required
✅ **Error Handling**: Failed syncs are logged and machine status is updated
✅ **Overlap Prevention**: `withoutOverlapping()` prevents multiple syncs running simultaneously
✅ **Background Execution**: `runInBackground()` doesn't block other scheduled tasks
✅ **Detailed Logging**: All sync operations are logged for monitoring
✅ **Smart Date Range**: Automatically determines date range based on last sync

### 8. Monitoring

#### Check Machine Sync Status:
View in: `Software → Master → Biometric Machine` list

Columns:
- **Last Sync At**: Timestamp of last successful sync
- **Sync Status**: pending/success/failed
- **Sync Error**: Error message if sync failed

#### Command Output:
The command provides detailed output:
```
Starting biometric attendance sync...
Found 2 active pull-based machine(s). Checking sync intervals...
  ⏭️  Machine #1 (Machine Name) - Skipped. Next sync: 2026-01-16 15:30:00
  🔄 Syncing Machine #2 (Machine Name)...
    Date range: 2026-01-15 to 2026-01-16
    ✅ Success! Fetched: 150, Transformed: 150, Stored: 150

📊 Sync Summary:
  ✅ Synced: 1
  ⏭️  Skipped: 1
  ❌ Failed: 0
  📋 Total: 2
```

### 9. Troubleshooting

#### Issue: Sync not running
**Solution (Method 1 - Web Route):**
1. Verify the web route is accessible: `curl https://yourdomain.com/run-cron`
2. Check if external cron service is making requests (check service logs)
3. Verify route is not blocked by firewall or middleware
4. Check Laravel logs for "run-cron on web request" entries
5. Verify machine is active and pull-based
6. Check logs: `storage/logs/biometric-sync-YYYY-MM-DD.log`

**Solution (Method 2 - Server Cron):**
1. Verify cron is running: `crontab -l`
2. Check scheduler is working: `php artisan schedule:run`
3. Verify machine is active and pull-based
4. Check logs: `storage/logs/biometric-sync-YYYY-MM-DD.log`

#### Issue: Sync failing
**Solution:**
1. Check machine credentials in Biometric Machine form
2. Verify API URL is accessible
3. Check `last_sync_error` field in database
4. Review Laravel logs for detailed error messages

#### Issue: Interval not respected
**Solution:**
1. Verify `sync_interval_minutes` is set correctly
2. Check `last_sync_at` is being updated after sync
3. Ensure cron is running every minute

### 10. Manual Sync

You can still manually sync a machine from:
- **Attendance Module**: "Sync Now" button
- **Biometric Machine Module**: "Sync Attendance" button
- **API Endpoint**: `POST /software/attendance/sync-biometric`

Manual sync will update `last_sync_at`, which resets the automatic sync timer.

### 11. Notes

- Only **pull-based providers** (eTimeOffice, Mintra) are synced automatically
- **Push-based providers** (Minop) are excluded (they send data via webhooks)
- Sync runs every minute, but each machine syncs only when its interval elapses
- Multiple machines can have different sync intervals (e.g., Machine 1: 5 min, Machine 2: 60 min)
- Failed syncs are logged but don't prevent future syncs
