# Attendance Import Feature - Cursor AI Prompt

## Context
I need to add a **multi-format attendance import feature** to my Laravel HRMS system that supports Excel (`.xls`, `.xlsx`) and PDF files. The system already has a similar employee import feature that I want to follow as a pattern. The attendance import should:
- Support multiple file formats (Excel, PDF) with automatic format detection
- Match employees by `biometric_user_id`, `employee_code`, or `employee_name`
- Automatically detect and remove duplicate entries
- **Automatically queue large files** (based on file size or row count) for background processing
- Support various customer-specific formats (including PDF reports with different column structures)

## Requirements

### 1. **Import Functionality**
- Create an attendance import feature similar to the existing employee import (`app/Imports/EmployeeImport.php`)
- Support multiple file formats:
  - **Excel**: `.xls`, `.xlsx`
  - **PDF**: `.pdf` (parse PDF reports and extract table data)
- Create import class: `app/Imports/AttendanceImport.php`
- Create PDF parser class: `app/Services/AttendancePdfParser.php` (for PDF extraction)
- Create queue job: `app/Jobs/ProcessAttendanceImport.php` (for large files)
- Follow the same pattern as `EmployeeImport` class
- **Automatic Queue Detection**: If file size > 5MB OR row count > 1000, automatically dispatch to queue

### 2. **Employee Matching Logic**
The import should match employees using the following priority order:
1. **Primary**: Match by `biometric_user_id` (from `employees` table)
2. **Secondary**: Match by `employee_code` (from `employees` table) 
3. **Tertiary**: Match by `employee_name` (search in `full_name`, `first_name`, `middle_name` fields)

**Important Notes:**
- The `employees` table has columns: `employee_code`, `biometric_user_id`, `full_name`, `first_name`, `middle_name`
- Employee matching should be scoped to the current `company_id` to prevent cross-company matches
- If employee is not found, log the error and skip that row (don't fail the entire import)

### 3. **Duplicate Detection & Auto-Removal**
- Detect duplicates based on: `employee_id` + `attendance_date` + `punch_in_time` + `attendace_type`
- Before inserting, check if a record already exists with the same combination
- If duplicate found:
  - **Option 1**: Skip the duplicate row (recommended)
  - **Option 2**: Update the existing record with new data
- Log all duplicate detections in the import summary

### 4. **Multi-Format Support & Column Mapping**

#### 4.1 **Excel Format Support**
The import should support **multiple column name variations** for flexibility:

**Required Columns (at least one identifier must be present):**
- Employee Identifier: `employee_code` OR `biometric_user_id` OR `employee_name` OR `emp_code` OR `card_no` OR `S.No Employee Code`
- `attendance_date` OR `date` OR `attendance_dt` OR `Attendance Date` (format: YYYY-MM-DD or DD/MM/YYYY)
- `punch_in_time` OR `punch_time` OR `time` OR `punch_in` OR `A. InTime` OR `InTime` (format: HH:MM:SS or HH:MM)
- `attendace_type` OR `attendance_type` OR `type` OR `punch_type` (values: 'in', 'out')
  - **Note**: If only `punch_in_time` is provided, default to 'in'
  - If `punch_out_time` OR `A.OutTime` OR `OutTime` is provided, create separate 'out' record

**Optional Columns:**
- `punch_out_time` OR `A.OutTime` OR `OutTime` (format: HH:MM:SS or HH:MM) - Creates 'out' record
- `shift_id` OR `shift` OR `Shift` (match by shift name or ID)
- `remark` OR `remarks` OR `notes` OR `Remarks`
- `status` OR `Status` (P = Present, A = Absent) - Used for validation
- `punch_id` OR `punchId` OR `transaction_id`
- `txn_id` OR `transaction_id`
- `device_serial` OR `device_serial_number`
- `device_ip` OR `device_ip_address`
- `W. Duration` OR `working_duration` OR `duration` (for reference)
- `OT` OR `overtime` (for reference)

#### 4.2 **PDF Format Support**
Support PDF attendance reports (like Daily Basic Attendance Report format):

**PDF Column Mapping:**
- `S.No Employee Code` → Extract employee code (may be combined with S.No)
- `Employee Name` → employee_name
- `Shift` → shift_id (match by name)
- `A. InTime` → punch_in_time
- `A.OutTime` → punch_out_time (create separate 'out' record)
- `Status` → P (Present) or A (Absent)
- `Remarks` → remark
- `Attendance Date` → Extract from report header (e.g., "Attendance Date- 18-Nov-2025")

**PDF Parsing Requirements:**
- Use PDF parsing library (e.g., `smalot/pdfparser` or `spatie/pdf`)
- Extract table data from PDF
- Handle multi-page PDFs
- Parse date from report headers
- Handle merged cells and complex table structures
- Extract data from "Daily Basic Attendance Report" format

### 5. **Controller Methods**
Add to `app/Http/Controllers/software/AttendanceController.php`:

```php
// Display import form
public function import()
{
    // Similar to EmployeeController::import()
    // Show import page with sample file download link
    // Support multiple file types (Excel, PDF)
}

// Process import
public function import_store(Request $request)
{
    // Similar to EmployeeController::import_store()
    // Validate file type (xls, xlsx, pdf)
    // Detect file format automatically
    // Check file size/row count - if large, dispatch to queue
    // If small, process immediately
    // Show results or queue status
}

// Check import status (for queued imports)
public function import_status($importId)
{
    // Return import progress/status for queued jobs
    // Used for AJAX polling
}
```

### 6. **Routes**
Add to `routes/software.php` (similar to employee import routes):
```php
Route::get('attendance/import', [AttendanceController::class, 'import'])->name('import-attendance.index');
Route::post('attendance/import', [AttendanceController::class, 'import_store'])->name('import-attendance.store');
Route::get('attendance/import/status/{importId}', [AttendanceController::class, 'import_status'])->name('import-attendance.status');
```

### 7. **Import View**
Create `resources/views/software/modules/master/attendance/import.blade.php`:
- Similar structure to `resources/views/software/modules/employee/import.blade.php`
- Include:
  - File upload form
  - Sample file download link
  - Instructions for Excel format
  - Import progress/results display
  - Error summary table

### 8. **Sample Excel Template**
Create sample import template: `public/sample-file/ocean-hrms-attendance-import-sample.xlsx`

**Template should include:**
- Header row with column names
- 2-3 example rows with sample data
- Data validation dropdowns where applicable (e.g., attendance_type: 'in', 'out')
- Format instructions in comments or separate sheet

**Required columns in sample:**
- `employee_code` (example: "EMP001")
- `attendance_date` (example: "2024-01-15" or "15/01/2024")
- `punch_in_time` (example: "09:00:00" or "09:00")
- `attendace_type` (example: "in" or "out")
- `shift_id` (optional, example: "1" or "Morning Shift")
- `remark` (optional)

### 9. **Attendance Model Structure**
The `Attendance` model (`app/Models/Attendance.php`) has these fillable fields:
- `company_id` (required)
- `employee_id` (required, will be resolved from employee matching)
- `shift_id` (optional)
- `attendance_date` (required, date format)
- `create_date` (default: current date)
- `punch_in_time` (required, time format)
- `attendace_type` (required: 'in' or 'out')
- `remark` (optional)
- `status` (default: 'active')
- `punch_id` (optional)
- `txn_id` (optional)
- `records_source` (set to 'excel_import' for imported records)
- `requested_data` (optional, JSON of import data)
- `device_serial` (optional)
- `device_ip` (optional)
- `created_by` (current user ID)

### 10. **Import Processing Logic**

#### 10.1 **File Upload & Format Detection:**
1. Accept files: `.xls`, `.xlsx`, `.pdf`
2. Detect file format automatically
3. Check file size and estimate row count
4. **Queue Decision Logic:**
   ```php
   $fileSize = $file->getSize(); // bytes
   $estimatedRows = $this->estimateRowCount($file); // for Excel
   $shouldQueue = ($fileSize > 5 * 1024 * 1024) || ($estimatedRows > 1000);
   
   if ($shouldQueue) {
       // Dispatch to queue
       ProcessAttendanceImport::dispatch($filePath, $companyId, $userId);
       return redirect()->back()->with('info', 'Large file queued for processing. You will be notified when complete.');
   } else {
       // Process immediately
       $this->processImport($file);
   }
   ```

#### 10.2 **Format-Specific Processing:**

**For Excel Files:**
- Use `Maatwebsite\Excel` to read data
- Support multiple sheet reading
- Handle header row variations

**For PDF Files:**
- Use PDF parser to extract text
- Identify table structure
- Extract attendance data from tables
- Parse date from headers (e.g., "Attendance Date- 18-Nov-2025")
- Handle multi-page PDFs

#### 10.3 **Validation:**
   - Validate file type (xls, xlsx, pdf)
   - Validate required columns/fields exist
   - Validate company_id matches authenticated user's company
   - Validate date formats (multiple formats)
   - Validate time formats (HH:MM:SS, HH:MM)
   - Validate attendance_type values ('in', 'out') or infer from data

2. **Employee Matching:**
   ```php
   // Priority 1: biometric_user_id (primary for biometric systems)
   if ($biometricUserId) {
       $employee = Employee::where('company_id', $companyId)
           ->where('biometric_user_id', $biometricUserId)
           ->first();
   }
   
   // Priority 2: employee_code
   if (!$employee && $employeeCode) {
       $employee = Employee::where('company_id', $companyId)
           ->where('employee_code', $employeeCode)
           ->first();
   }
   
   // Priority 3: employee_name (search in full_name, first_name, middle_name)
   if (!$employee && $employeeName) {
       $employee = Employee::where('company_id', $companyId)
           ->where(function($q) use ($employeeName) {
               $q->where('full_name', 'LIKE', "%{$employeeName}%")
                 ->orWhere('first_name', 'LIKE', "%{$employeeName}%")
                 ->orWhere(DB::raw("CONCAT(first_name, ' ', middle_name)"), 'LIKE', "%{$employeeName}%");
           })
           ->first();
   }
   ```
   
   **Special Handling for PDF Format:**
   - If "S.No Employee Code" column contains combined data (e.g., "11" where 1 is S.No and 1 is code), split it
   - Handle employee codes that may be numeric strings
   - Handle cases where employee name is "-" or empty

3. **Duplicate Check:**
   ```php
   $duplicate = Attendance::where('company_id', $companyId)
       ->where('employee_id', $employeeId)
       ->where('attendance_date', $attendanceDate)
       ->where('punch_in_time', $punchInTime)
       ->where('attendace_type', $attendanceType)
       ->first();
   
   if ($duplicate) {
       // Skip or update based on requirement
       continue; // Skip duplicate
   }
   ```

4. **Shift Matching:**
   - If `shift_id` is provided as name, find shift by name
   - If `shift_id` is numeric, use directly
   - If not provided, you may need to get default shift from employee's employment details

5. **Date/Time Parsing:**
   - Support multiple date formats: YYYY-MM-DD, DD/MM/YYYY, DD-MM-YYYY, DD-Mon-YYYY (e.g., "18-Nov-2025")
   - Support multiple time formats: HH:MM:SS, HH:MM
   - Handle Excel date serial numbers if needed
   - For PDF: Extract date from report header if not in row data
   - Handle "00:00" time as null/empty (no punch)

6. **Punch Type Inference:**
   - If `punch_in_time` exists and `punch_out_time` is empty/null → create 'in' record
   - If `punch_out_time` exists → create 'out' record (even if punch_in_time is empty)
   - If both exist → create TWO records: one 'in' and one 'out'
   - If `attendace_type` is explicitly provided, use that value

7. **Status Handling (from PDF):**
   - If Status = "P" or "Present" → Process attendance
   - If Status = "A" or "Absent" → Skip row (or create absent record if needed)
   - If Status = "$P$" → Process as Present

### 11. **Queue Job Implementation**

Create `app/Jobs/ProcessAttendanceImport.php`:
```php
class ProcessAttendanceImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    protected $filePath;
    protected $companyId;
    protected $userId;
    protected $importFileId; // Store import file record ID
    
    public function handle()
    {
        // Process import
        // Update import status in database
        // Send notification when complete
    }
    
    public function failed(\Throwable $exception)
    {
        // Handle job failure
        // Update import status to 'failed'
        // Log error
    }
}
```

**Import File Tracking Model:**
Create `app/Models/AttendanceImportFile.php` (similar to `EmployeeImportFile`):
- Track: file_path, company_id, user_id, status (pending/processing/completed/failed)
- Track: total_rows, success_count, failed_count, duplicate_count
- Store errors as JSON
- Track processing start/end times

### 12. **Import Summary & Error Handling**
- Track:
  - Total rows processed
  - Successfully imported rows
  - Failed rows (with reasons)
  - Duplicate rows skipped
  - Employee not found errors
  - Format detection info (Excel/PDF)
- Display summary after import completion (or queue status for large files)
- For queued imports: Show progress via AJAX polling
- Log all errors with row numbers
- Return detailed error report (similar to employee import)
- Send notification email when queued import completes

### 13. **Export Format Compatibility**
The import should be compatible with the existing export format from `app/Exports/AttendanceExport.php`:
- Ensure exported Excel can be re-imported
- Handle column name variations gracefully
- Support importing from exported PDF reports (if PDF export is added later)

### 14. **Additional Requirements**
- Add "Import" button/link in attendance index page
- Add import permission check (use `excel_permission` similar to export)
- Follow existing code style and patterns
- Use Laravel Excel (Maatwebsite\Excel) package for Excel files
- Use PDF parsing library (e.g., `smalot/pdfparser` or `spatie/pdf`) for PDF files
- Implement proper error handling and logging
- Add validation messages in user-friendly format
- **Queue Configuration**: Ensure queue worker is running (`php artisan queue:work`)
- **File Storage**: Store uploaded files temporarily in `storage/app/attendance-imports/`
- **Cleanup**: Delete temporary files after processing (or after 7 days)
- **Progress Tracking**: For queued imports, provide real-time progress updates via AJAX
- **Notification**: Send email/notification when large file import completes

## Reference Files
- **Employee Import Class**: `app/Imports/EmployeeImport.php`
- **Employee Controller Import Methods**: `app/Http/Controllers/software/EmployeeController.php` (lines 771-820+)
- **Employee Import View**: `resources/views/software/modules/employee/import.blade.php`
- **Attendance Model**: `app/Models/Attendance.php`
- **Attendance Controller**: `app/Http/Controllers/software/AttendanceController.php`
- **Attendance Export**: `app/Exports/AttendanceExport.php`
- **Employee Model**: `app/Models/Employee.php`
- **Routes**: `routes/software.php` (lines 309-310 for employee import pattern)

## Expected Output
1. `app/Imports/AttendanceImport.php` - Excel import class
2. `app/Services/AttendancePdfParser.php` - PDF parsing service
3. `app/Jobs/ProcessAttendanceImport.php` - Queue job for large files
4. `app/Models/AttendanceImportFile.php` - Import tracking model
5. Migration for `attendance_import_files` table
6. Updated `app/Http/Controllers/software/AttendanceController.php` - Import methods
7. `resources/views/software/modules/master/attendance/import.blade.php` - Import view with progress tracking
8. `public/sample-file/ocean-hrms-attendance-import-sample.xlsx` - Sample Excel template
9. Updated `routes/software.php` - Import routes
10. Updated attendance index view to include import button
11. JavaScript for AJAX progress polling (for queued imports)

## Testing Checklist
- [ ] Import Excel with employee_code matching
- [ ] Import Excel with biometric_user_id matching
- [ ] Import Excel with employee_name matching
- [ ] Import PDF with Daily Basic Attendance Report format
- [ ] Import PDF with multiple pages
- [ ] Import PDF with date extraction from header
- [ ] Duplicate detection and skipping
- [ ] Multiple date/time format support
- [ ] Queue automatic dispatch for large files (>5MB or >1000 rows)
- [ ] Immediate processing for small files
- [ ] Queue job processing and status updates
- [ ] Progress tracking via AJAX for queued imports
- [ ] Error handling for invalid data
- [ ] Import summary display
- [ ] Permission checks
- [ ] Company scope validation
- [ ] File cleanup after processing
- [ ] Notification on completion (for queued imports)

---

**Please implement this feature following the existing patterns in the codebase, especially the employee import functionality as a reference.**

## PDF Format Example (Based on Daily Basic Attendance Report)

The system should handle PDF reports with this structure:
```
Daily Basic Attendance Report
17-Nov-2025 To 19-Nov-2025 Generated On: 20-Nov-2025 10:06 AM

Attendance Date- 18-Nov-2025
Department:- DefaultDepartment

| S.No Employee Code | Employee Name | Shift | A. InTime | A.OutTime | Status | Remarks |
| 11 | nikunj | GS | 07:00:07 | 20:14:08 | P | |
| 22 | sandip | NA | 00:00 | 00:00 | A | |
```

**Key Points:**
- Extract date from "Attendance Date- DD-Mon-YYYY" format
- Handle "S.No Employee Code" where code may be combined with serial number
- Parse "A. InTime" and "A.OutTime" columns
- Status "P" = Present, "A" = Absent
- Handle "00:00" times as no punch
- Create separate 'in' and 'out' records when both times exist

