# Biometric Machine Integration Plan
## Company-wise Biometric Machine Setup (eTimeOffice, Mintra)

### Overview
Implement company-wise biometric machine integration supporting multiple providers (eTimeOffice, Mintra) with a constraint that only one machine per company can be active at a time.

### Integration Patterns
The system supports two types of integration patterns:

1. **Push-based Integration (Minop)** - Provider sends data to our server
   - Provider pushes attendance data via webhook endpoints
   - Our server receives and processes the data
   - Real-time data delivery
   - No need for scheduled sync jobs
   - No API credentials required
   - **Data Flow:** Provider → Webhook Endpoint → Our Database

2. **Pull-based Integration (eTimeOffice, Mintra)** - We request data from provider API
   - Our server makes API calls to fetch attendance data
   - Requires API credentials (username/password)
   - Scheduled sync jobs to periodically fetch data
   - Manual sync option available
   - **Data Flow:** Our Server → API Request → Provider API → Our Database

### Key Differences Summary

| Feature | Minop (Push) | eTimeOffice/Mintra (Pull) |
|---------|--------------|---------------------------|
| Data Direction | Provider → Our Server | Our Server → Provider |
| API Credentials | Not Required | Required |
| Webhook Endpoints | Yes (receive data) | No |
| Sync Jobs | Not Needed | Required (scheduled) |
| Manual Sync | Not Applicable | Available |
| Real-time | Yes | No (periodic sync) |
| Connection Test | Not Applicable | Available |

---

## 📋 Implementation Plan

### Phase 1: Database Schema Updates

#### 1.1 Update `biometric_machines` Table
**Migration File:** `database/migrations/YYYY_MM_DD_HHMMSS_add_provider_fields_to_biometric_machines_table.php`

**Add Columns:**
- `provider_type` (enum: 'minop', 'etimeoffice', 'mintra') - Provider identifier
- `api_url` (string, nullable) - API endpoint URL for the provider
- `api_username` (string, nullable) - API authentication username
- `api_password` (string, nullable) - Encrypted API authentication password
- `is_active` (boolean, default: false) - Flag to mark active machine per company
- `last_sync_at` (timestamp, nullable) - Last successful sync timestamp
- `last_sync_status` (enum: 'success', 'failed', 'pending', nullable) - Last sync operation status
- `last_sync_error` (text, nullable) - Error message from last failed sync
- `sync_interval_minutes` (integer, default: 60) - Sync frequency in minutes

**Add Index:**
- Unique constraint: `company_id` + `is_active = true` (only one active machine per company)
- Index on: `provider_type`, `company_id`

#### 1.2 Update Model
- Update `BiometricMachine` model fillable fields
- Add encryption for `api_password` field
- Add scope methods: `active()`, `byProvider()`, `forCompany()`, `pullBased()`, `pushBased()`
- Add helper methods:
  - `isPullBasedProvider()` - Check if provider requires API sync (eTimeOffice, Mintra)
  - `isPushBasedProvider()` - Check if provider uses webhooks (Minop)
  - `shouldShowSyncButton()` - Determine if sync button should be shown
  - `getLastSyncStatus()` - Get last sync status (success, failed, never_synced, etc.)
  - `getLastSyncTimeAgo()` - Get human-readable time since last sync
  - `canSyncNow()` - Check if sync is allowed (active machine, pull-based, has credentials)
- Add relationship methods if needed

---

### Phase 2: Service Layer - Provider Integration

#### 2.1 Create Provider Interface
**File:** `app/Services/Biometric/Contracts/BiometricProviderInterface.php`

**Methods:**
- `authenticate()` - Authenticate with provider API (for pull-based providers)
- `fetchAttendance($startDate, $endDate)` - Fetch attendance records (for pull-based providers)
- `syncEmployees()` - Sync employee data (optional)
- `testConnection()` - Test API connection (for pull-based providers)
- `getProviderName()` - Return provider name
- `getIntegrationType()` - Return 'push' or 'pull' integration type

#### 2.2 Create eTimeOffice Service (Pull-based)
**File:** `app/Services/Biometric/ETimeOfficeService.php`

**Implementation:**
- Implement `BiometricProviderInterface`
- **Integration Type:** Pull-based (we request data from their API)
- API Base URL: `https://api.etimeoffice.com/api/` (configurable per machine)
- Authentication: Username/Password based
- **Endpoints to implement:**
  - Authentication: POST `/auth/login` - Get authentication token
  - Attendance: GET `/attendance` (with date filters) - Fetch attendance records
  - Employees: GET `/employees` - Sync employee data (optional)
- **Data Flow:**
  1. Authenticate with API credentials
  2. Make GET request to fetch attendance data
  3. Process and store attendance records in our database
  4. Handle pagination if API supports it
- Error handling and logging
- Token management (store/refresh tokens if needed)

#### 2.3 Create Mintra Service (Pull-based)
**File:** `app/Services/Biometric/MintraService.php`

**Implementation:**
- Implement `BiometricProviderInterface`
- **Integration Type:** Pull-based (we request data from their API)
- API Base URL: (To be confirmed from Mintra documentation, configurable per machine)
- Authentication: (To be confirmed - likely Username/Password or API Key)
- **Endpoints to implement:**
  - Authentication endpoint (if required)
  - Attendance fetch endpoint with date filters
  - Employee sync endpoint (optional)
- **Data Flow:**
  1. Authenticate with API credentials
  2. Make API request to fetch attendance data
  3. Process and store attendance records in our database
- Error handling and logging

#### 2.4 Create Service Factory
**File:** `app/Services/Biometric/BiometricServiceFactory.php`

**Purpose:**
- Factory pattern to instantiate correct provider service based on `provider_type`
- Returns appropriate service instance: ETimeOfficeService, MintraService, or MinopService
- Handle different integration types (push vs pull)
- For Minop: No service needed (uses existing webhook controller)
- For eTimeOffice/Mintra: Returns pull-based service instance

---

### Phase 3: Controller Updates

#### 3.1 Update BiometricMachineController
**File:** `app/Http/Controllers/software/BiometricMachineController.php`

**Updates:**
- Update `store()` method to:
  - Validate provider_type
  - Encrypt API password
  - Deactivate other machines when activating new one
  - Test connection before saving (for pull-based providers)
- Update `update()` method with same logic
- Update `index()` method to:
  - Pass sync status information to view
  - Include helper data for sync button visibility logic
- Update `show()` or `edit()` method to include sync status
- Add `testConnection($id)` method (for pull-based providers only)
- Add `toggleActive($id)` method to activate/deactivate
- Add `syncAttendance($id)` method for manual sync (pull-based providers only)
- Add `getSyncStatus($id)` method to return sync status info
- Add validation to prevent sync operations on push-based providers
- Update validation rules in request class

#### 3.2 Create API Controllers for Providers

**3.2.1 ETimeOffice API Controller (Pull-based)**
**File:** `app/Http/Controllers/Api/ETimeOfficeAttendanceController.php`

**Note:** eTimeOffice is pull-based, so NO webhook endpoint needed. We fetch data from their API.

**Endpoints:**
- `POST /api/biometric/etimeoffice/sync/{machine_id}` - Manual sync trigger (fetches data from eTimeOffice API)
- `GET /api/biometric/etimeoffice/test/{machine_id}` - Test connection (tests API authentication)
- `GET /api/biometric/etimeoffice/sync-status/{machine_id}` - Get last sync status

**Implementation:**
- Uses `ETimeOfficeService` to authenticate and fetch data
- Processes fetched attendance records
- Stores records in `attendances` table
- Returns sync results (success count, error count, etc.)

**3.2.2 Mintra API Controller (Pull-based)**
**File:** `app/Http/Controllers/Api/MintraAttendanceController.php`

**Note:** Mintra is pull-based, so NO webhook endpoint needed. We fetch data from their API.

**Endpoints:**
- `POST /api/biometric/mintra/sync/{machine_id}` - Manual sync trigger (fetches data from Mintra API)
- `GET /api/biometric/mintra/test/{machine_id}` - Test connection (tests API authentication)
- `GET /api/biometric/mintra/sync-status/{machine_id}` - Get last sync status

**Implementation:**
- Uses `MintraService` to authenticate and fetch data
- Processes fetched attendance records
- Stores records in `attendances` table
- Returns sync results

**3.2.3 Minop Controller (Push-based - Already Exists)**
**File:** `app/Http/Controllers/Api/MinopAttendanceController.php`

**Note:** Minop is push-based. Provider sends data to our webhook endpoints.
- Existing endpoints remain unchanged:
  - `POST /api/biometric/minop/say-hello/{app_key?}` - Device registration
  - `POST /api/biometric/attendance/{biomax_company_name?}` - Receive attendance webhook
- No sync endpoint needed (data is pushed in real-time)

---

### Phase 4: Request Validation Updates

#### 4.1 Update BiometricMachineRequest
**File:** `app/Http/Requests/BiometricMachineRequest.php`

**New Validation Rules:**
- `provider_type`: required, in:minop,etimeoffice,mintra
- `api_url`: required_if:provider_type,etimeoffice,mintra
- `api_username`: required_if:provider_type,etimeoffice,mintra
- `api_password`: required_if:provider_type,etimeoffice,mintra
- Conditional validation based on provider type
- Update unique constraint to consider provider_type

---

### Phase 5: Background Jobs & Scheduling

#### 5.1 Create Sync Jobs (For Pull-based Providers Only)
**File:** `app/Jobs/SyncBiometricAttendanceJob.php`

**Purpose:**
- Queue job to sync attendance from active biometric machines
- **Only process pull-based providers** (eTimeOffice, Mintra)
- **Skip push-based providers** (Minop - they send data via webhooks)
- Process each active machine per company
- Use appropriate provider service based on machine type
- Fetch attendance data for date range (default: last 24 hours or since last sync)
- Handle errors gracefully with retry logic
- Log sync results
- Update `last_sync_at` timestamp on successful sync

**Logic:**
```php
// Only sync pull-based providers
if ($machine->provider_type === 'etimeoffice' || $machine->provider_type === 'mintra') {
    try {
        // Update sync status to pending
        $machine->update(['last_sync_status' => 'pending']);
        
        // Fetch data from provider API
        $service = BiometricServiceFactory::make($machine->provider_type);
        $attendanceData = $service->fetchAttendance($startDate, $endDate);
        
        // Process and store records
        // ... process attendance records ...
        
        // Update sync status to success
        $machine->update([
            'last_sync_at' => now(),
            'last_sync_status' => 'success',
            'last_sync_error' => null
        ]);
    } catch (\Exception $e) {
        // Update sync status to failed
        $machine->update([
            'last_sync_status' => 'failed',
            'last_sync_error' => $e->getMessage()
        ]);
        throw $e; // Re-throw for job retry logic
    }
}
// Skip Minop (push-based, no sync needed)
```

#### 5.2 Schedule Automated Sync (For Pull-based Providers Only)
**File:** `app/Console/Kernel.php`

**Updates:**
- Add scheduled task to run `SyncBiometricAttendanceJob` periodically
- Default: Every 60 minutes (configurable per machine via `sync_interval_minutes`)
- Only process machines with `is_active = true`
- **Only sync pull-based providers** (eTimeOffice, Mintra)
- **Skip push-based providers** (Minop - they send data via webhooks automatically)

**Scheduling Logic:**
```php
// In Kernel.php schedule method
$schedule->call(function () {
    // Get all active pull-based machines
    $machines = BiometricMachine::where('is_active', true)
        ->whereIn('provider_type', ['etimeoffice', 'mintra'])
        ->get();
    
    foreach ($machines as $machine) {
        // Check if sync interval has passed
        if ($machine->shouldSync()) {
            SyncBiometricAttendanceJob::dispatch($machine);
        }
    }
})->everyFifteenMinutes(); // Check every 15 min, but sync based on machine interval
```

---

### Phase 6: Routes

#### 6.1 Update Web Routes
**File:** `routes/webhook.php`

**Add Routes:**
```php
// eTimeOffice routes (Pull-based - no webhook, only sync endpoints)
Route::post('biometric/etimeoffice/sync/{machine_id}', [ETimeOfficeAttendanceController::class, 'sync'])->name('biometric.etimeoffice.sync');
Route::get('biometric/etimeoffice/test/{machine_id}', [ETimeOfficeAttendanceController::class, 'testConnection'])->name('biometric.etimeoffice.test');
Route::get('biometric/etimeoffice/sync-status/{machine_id}', [ETimeOfficeAttendanceController::class, 'syncStatus'])->name('biometric.etimeoffice.sync-status');

// Mintra routes (Pull-based - no webhook, only sync endpoints)
Route::post('biometric/mintra/sync/{machine_id}', [MintraAttendanceController::class, 'sync'])->name('biometric.mintra.sync');
Route::get('biometric/mintra/test/{machine_id}', [MintraAttendanceController::class, 'testConnection'])->name('biometric.mintra.test');
Route::get('biometric/mintra/sync-status/{machine_id}', [MintraAttendanceController::class, 'syncStatus'])->name('biometric.mintra.sync-status');

// Minop routes (Push-based - already exist, keep as is)
// Route::post('biometric/minop/say-hello/{app_key?}', [MinopAttendanceController::class, 'say_hello']);
// Route::post('biometric/attendance/{biomax_company_name?}', [MinopAttendanceController::class, 'receive_attendance']);
```

#### 6.2 Update Software Routes
**File:** `routes/software.php`

**Add Routes:**
```php
Route::post('biometric-machines/{id}/test-connection', [BiometricMachineController::class, 'testConnection'])->name('biometric-machines.test-connection');
Route::post('biometric-machines/{id}/toggle-active', [BiometricMachineController::class, 'toggleActive'])->name('biometric-machines.toggle-active');
Route::post('biometric-machines/{id}/sync-attendance', [BiometricMachineController::class, 'syncAttendance'])->name('biometric-machines.sync-attendance');
```

---

### Phase 7: Frontend/Views Updates

#### 7.1 Update Form View
**File:** `resources/views/software/modules/master/biometric-machine/form.blade.php`

**Add Fields:**
- Provider Type dropdown (Minop, eTimeOffice, Mintra)
  - Show info tooltip: "Minop: Push-based (receives data), eTimeOffice/Mintra: Pull-based (fetches data)"
- API URL input (conditional - shown for eTimeOffice & Mintra only)
- API Username input (conditional - shown for eTimeOffice & Mintra only)
- API Password input (conditional, password type - shown for eTimeOffice & Mintra only)
- "Test Connection" button (for eTimeOffice & Mintra only - tests API authentication)
- "Set as Active" checkbox/toggle
- Sync Interval input (number, minutes - shown for eTimeOffice & Mintra only, default: 60)
- Info message: "For Minop: No API credentials needed. Device will send data automatically."

#### 7.2 Update Index/List View
**File:** `resources/views/software/modules/master/biometric-machine/index.blade.php`

**Updates:**
- Display provider type column with integration type badge (Push/Pull)
- Show active/inactive status prominently
- Add "Set Active" button/action
- **Sync Button Logic (Provider & Last Sync Based):**
  - Show "Sync Now" button ONLY for pull-based providers (eTimeOffice, Mintra)
  - Hide "Sync Now" button for push-based providers (Minop)
  - Display last sync information:
    - Last sync timestamp with time ago (e.g., "2 hours ago", "Never synced")
    - Last sync status badge (Success/Failed/Pending/Never)
    - Sync status indicator (green/yellow/red dot)
  - Show sync button with appropriate state:
    - Enabled: Machine is active, pull-based provider, has credentials
    - Disabled: Machine inactive, push-based provider, missing credentials, or sync in progress
  - Add tooltip on sync button showing last sync details
- Add "Test Connection" button (only for pull-based providers)
  - Disabled/hidden for Minop
- Show integration type indicator (Push/Pull)
- Add sync status column showing:
  - Last sync time (human-readable: "2 hours ago", "Never")
  - Sync status badge (Success/Failed/Never)
  - Next sync time (if scheduled sync is enabled)

**Sync Button Visibility Rules:**
```php
// In Controller or View
@if($machine->isPullBasedProvider() && $machine->is_active)
    <button class="sync-btn" 
            data-machine-id="{{ $machine->id }}"
            title="Last sync: {{ $machine->getLastSyncTimeAgo() }}">
        Sync Now
        @if($machine->last_sync_at)
            <small>({{ $machine->getLastSyncTimeAgo() }})</small>
        @endif
    </button>
@endif
```

#### 7.3 Add JavaScript for Dynamic Fields
**File:** `resources/views/software/modules/master/biometric-machine/form.blade.php` (inline script)

**Functionality:**
- Show/hide API fields based on provider type selection
  - Show for: eTimeOffice, Mintra (pull-based)
  - Hide for: Minop (push-based)
- Show/hide sync interval field (only for pull-based providers)
- AJAX call for "Test Connection" button (only for pull-based providers)
- Display connection test results (success/error messages)
- Prevent form submission if connection test fails (optional, with warning)
- Update form labels/help text based on provider type

#### 7.4 Add JavaScript for Sync Button (Index View)
**File:** `resources/views/software/modules/master/biometric-machine/index.blade.php` (inline script)

**Functionality:**
- Handle "Sync Now" button click via AJAX
- Show loading state during sync operation
- Display sync progress/status updates
- Update last sync timestamp after successful sync
- Show success/error notifications
- Refresh sync status indicators
- Disable sync button during sync operation to prevent duplicate requests
- Auto-refresh sync status periodically (optional, for active machines)

---

### Phase 8: Business Logic & Constraints

#### 8.1 Single Active Machine Constraint
**Implementation:**
- When activating a machine (`is_active = true`), automatically set all other machines for the same company to `is_active = false`
- Apply in `BiometricMachineController@store` and `BiometricMachineController@update`
- Add database constraint if possible (unique index with condition)

#### 8.2 Provider-Specific Logic

**Minop (Push-based):**
- Keep existing logic, no changes needed
- Provider sends data via webhook endpoints
- No API credentials needed
- No scheduled sync required
- Real-time data delivery
- **Sync Button:** Never shown (push-based, no manual sync)

**eTimeOffice (Pull-based):**
- Use `ETimeOfficeService` for API communication
- Handle API authentication (username/password)
- Fetch attendance data via API calls
- Scheduled sync jobs required
- Manual sync option available
- Store API credentials (encrypted)
- **Sync Button:** Shown when machine is active and has credentials
- Track sync status: `last_sync_at`, `last_sync_status`, `last_sync_error`

**Mintra (Pull-based):**
- Use `MintraService` for API communication
- Handle API authentication (method to be confirmed)
- Fetch attendance data via API calls
- Scheduled sync jobs required
- Manual sync option available
- Store API credentials (encrypted)
- **Sync Button:** Shown when machine is active and has credentials
- Track sync status: `last_sync_at`, `last_sync_status`, `last_sync_error`

#### 8.3 Sync Button Display Logic

**Rules for showing "Sync Now" button:**
1. Provider must be pull-based (`provider_type` = 'etimeoffice' OR 'mintra')
2. Machine must be active (`is_active` = true)
3. Machine must have API credentials (api_url, api_username, api_password not null)
4. Sync button state:
   - **Enabled:** All conditions met, sync not in progress
   - **Disabled:** Machine inactive, missing credentials, or sync in progress
   - **Hidden:** Push-based provider (Minop)

**Last Sync Information Display:**
- Show last sync timestamp with human-readable format (e.g., "2 hours ago")
- Show sync status badge (Success/Failed/Pending/Never)
- Show sync error message if last sync failed (tooltip or expandable section)
- Show next scheduled sync time (if applicable)

---

### Phase 9: Error Handling & Logging

#### 9.1 Logging
- Log all API calls to biometric providers
- Log sync results (success/failure)
- Log connection test results
- Store error messages in database or log files

#### 9.2 Error Handling
- Graceful handling of API failures
- Retry logic for failed syncs
- User-friendly error messages
- Notification system for sync failures (optional)

---

### Phase 10: Testing & Documentation

#### 10.1 Testing Checklist
- [ ] Create machine with eTimeOffice provider
- [ ] Create machine with Mintra provider
- [ ] Test connection for each provider
- [ ] Verify single active machine constraint
- [ ] Test manual sync functionality
- [ ] Test automated sync job
- [ ] Verify attendance records are created correctly
- [ ] Test error scenarios (invalid credentials, network issues)
- [ ] Verify sync button visibility:
  - [ ] Sync button shown for active eTimeOffice machines with credentials
  - [ ] Sync button shown for active Mintra machines with credentials
  - [ ] Sync button hidden for Minop machines (push-based)
  - [ ] Sync button hidden for inactive machines
  - [ ] Sync button hidden for machines without credentials
- [ ] Verify last sync status display:
  - [ ] Shows "Never synced" for new machines
  - [ ] Shows last sync time correctly
  - [ ] Shows sync status badge (Success/Failed)
  - [ ] Shows error message on failed sync
- [ ] Test sync button states (enabled/disabled/hidden)

#### 10.2 Documentation
- API documentation for webhook endpoints
- User guide for setting up machines
- Provider-specific setup instructions
- Troubleshooting guide

---

## 🔄 Implementation Order

1. **Phase 1** - Database schema updates (Foundation)
2. **Phase 2** - Service layer (Core logic)
3. **Phase 4** - Request validation (Data integrity)
4. **Phase 3** - Controllers (API layer)
5. **Phase 6** - Routes (Routing)
6. **Phase 7** - Frontend (User interface)
7. **Phase 8** - Business logic (Constraints)
8. **Phase 5** - Background jobs (Automation)
9. **Phase 9** - Error handling (Reliability)
10. **Phase 10** - Testing (Quality assurance)

---

## 📝 Notes

1. **Integration Pattern Differences:**
   - **Minop (Push-based):** Provider sends data to our webhook endpoints. No API credentials needed. Real-time delivery.
   - **eTimeOffice (Pull-based):** We make API calls to fetch data. Requires API credentials. Scheduled sync needed.
   - **Mintra (Pull-based):** We make API calls to fetch data. Requires API credentials. Scheduled sync needed.

2. **Provider API Documentation:**
   - eTimeOffice API documentation may need to be obtained from their support
   - Mintra API documentation may need to be obtained from their support
   - Implementation may need adjustments based on actual API specifications
   - Verify exact API endpoints, authentication methods, and data formats

2. **Security:**
   - API passwords should be encrypted using Laravel's encryption
   - Consider using environment variables for sensitive data
   - Implement rate limiting for API endpoints

3. **Scalability:**
   - Consider queue system for sync jobs
   - Implement caching for frequently accessed data
   - Monitor performance with multiple companies

4. **Backward Compatibility:**
   - Existing Minop machines should continue to work
   - Set default `provider_type` to 'minop' for existing records
   - Migrate existing machines appropriately

---

## ✅ Acceptance Criteria

1. ✅ Multiple biometric providers (eTimeOffice, Mintra) can be configured per company
2. ✅ Only one machine per company can be active at a time
3. ✅ Pull-based providers (eTimeOffice, Mintra) can be configured with API credentials
4. ✅ Push-based provider (Minop) works without API credentials (webhook only)
5. ✅ Connection test functionality works for pull-based providers
6. ✅ Attendance data syncs correctly:
   - Minop: Receives data via webhooks (real-time)
   - eTimeOffice/Mintra: Fetches data via API (scheduled/manual)
7. ✅ Manual and automated sync functionality works for pull-based providers
8. ✅ Sync button visibility is controlled by provider type and last sync status
   - Shown only for pull-based providers (eTimeOffice, Mintra)
   - Hidden for push-based providers (Minop)
   - Enabled/disabled based on machine status and credentials
9. ✅ Last sync information is displayed (timestamp, status, errors)
10. ✅ UI clearly shows active/inactive status and integration type (Push/Pull)
11. ✅ Existing Minop integration continues to work (no breaking changes)
12. ✅ Error handling and logging is in place
13. ✅ Documentation is complete

---

## 🚀 Quick Start (After Implementation)

### For Push-based Provider (Minop):
1. Navigate to Biometric Machines module
2. Click "Add" to create new machine
3. Select provider type: "Minop"
4. Enter machine details (IP, Port, Serial Number)
5. No API credentials needed
6. Set machine as "Active" (will deactivate other machines)
7. Device will automatically send attendance data via webhooks

### For Pull-based Providers (eTimeOffice/Mintra):
1. Navigate to Biometric Machines module
2. Click "Add" to create new machine
3. Select provider type (eTimeOffice/Mintra)
4. Enter machine details and API credentials (URL, Username, Password)
5. Click "Test Connection" to verify API authentication
6. Set sync interval (default: 60 minutes)
7. Set machine as "Active" (will deactivate other machines)
8. Attendance will sync automatically (scheduled) or manually via "Sync Now" button
