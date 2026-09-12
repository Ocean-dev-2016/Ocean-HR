# Attendance Import Feature - Quick Prompt for Cursor AI

Add a **multi-format attendance import feature** (Excel + PDF) to my Laravel HRMS system with automatic queue processing for large files, following the existing employee import pattern.

## Key Requirements:

1. **Create Import Classes & Services:**
   - `app/Imports/AttendanceImport.php` - Excel import (Maatwebsite\Excel)
   - `app/Services/AttendancePdfParser.php` - PDF parsing service
   - `app/Jobs/ProcessAttendanceImport.php` - Queue job for large files
   - `app/Models/AttendanceImportFile.php` - Import tracking model

2. **Employee Matching** (priority order):
   - Match by `employee_code` (primary)
   - Match by `biometric_user_id` (secondary) 
   - Match by `employee_name` - search in `full_name`, `first_name`, `middle_name` (tertiary)
   - Scope all matches to current `company_id`

3. **Duplicate Detection**:
   - Check duplicates on: `employee_id` + `attendance_date` + `punch_in_time` + `attendace_type`
   - Auto-skip duplicates (don't insert)

4. **Multi-Format Support**:
   - **Excel**: Support column variations (`employee_code`, `biometric_user_id`, `employee_name`, `attendance_date`, `punch_in_time`, `punch_out_time`, `attendace_type`, etc.)
   - **PDF**: Parse "Daily Basic Attendance Report" format with columns:
     - `S.No Employee Code`, `Employee Name`, `Shift`, `A. InTime`, `A.OutTime`, `Status`, `Remarks`
     - Extract date from header: "Attendance Date- DD-Mon-YYYY"
     - Handle multi-page PDFs
   - **Auto Queue**: If file > 5MB OR rows > 1000, dispatch to queue for background processing
   - **Format Detection**: Automatically detect Excel vs PDF

5. **Controller Methods** (add to `AttendanceController.php`):
   - `import()` - Display import form (like `EmployeeController::import()`)
   - `import_store()` - Process import (like `EmployeeController::import_store()`)

6. **Routes** (add to `routes/software.php`):
   ```php
   Route::get('attendance/import', [AttendanceController::class, 'import'])->name('import-attendance.index');
   Route::post('attendance/import', [AttendanceController::class, 'import_store'])->name('import-attendance.store');
   ```

7. **Import View**: Create `resources/views/software/modules/master/attendance/import.blade.php`
   - Similar to `resources/views/software/modules/employee/import.blade.php`
   - Include file upload, sample download, instructions, error summary

8. **Sample Template**: Create `public/sample-file/ocean-hrms-attendance-import-sample.xlsx`
   - Columns: `employee_code`, `attendance_date`, `punch_in_time`, `attendace_type`, `shift_id` (optional), `remark` (optional)
   - Include 2-3 example rows

9. **Attendance Fields** (from `app/Models/Attendance.php`):
   - Required: `company_id`, `employee_id`, `attendance_date`, `punch_in_time`, `attendace_type`
   - Optional: `shift_id`, `remark`, `punch_id`, `txn_id`, `device_serial`, `device_ip`
   - Set `records_source` = 'excel_import'
   - Set `created_by` = current user ID

10. **Queue & Progress Tracking**:
    - Large files automatically queued (file > 5MB OR rows > 1000)
    - Track import status: pending/processing/completed/failed
    - AJAX polling for queued import progress
    - Email notification when queued import completes

11. **Import Summary**:
    - Track: total rows, success count, failed count, duplicates skipped
    - Show errors with row numbers
    - Display summary after import (or queue status for large files)

11. **Add Import Button**: Add "Import" link/button in attendance index page

**Reference Files:**
- `app/Imports/EmployeeImport.php` - Import pattern
- `app/Http/Controllers/software/EmployeeController.php` (lines 771-820) - Controller methods
- `resources/views/software/modules/employee/import.blade.php` - View template
- `app/Models/Attendance.php` - Model structure
- `routes/software.php` (lines 309-310) - Route pattern

**Important:** 
- Handle date/time format variations (including "DD-Mon-YYYY" from PDF)
- Support PDF parsing library (e.g., `smalot/pdfparser`)
- Auto-queue large files for background processing
- Validate all inputs, check permissions (`excel_permission`), ensure company scope validation
- Create separate 'in' and 'out' records when both punch_in_time and punch_out_time exist
- Handle "00:00" times as no punch
- Extract date from PDF report headers when not in row data

