-- ============================================
-- 3rd Party Attendance API - Database Queries
-- ============================================

-- 1. Check Recent 3rd Party Attendance Records
SELECT 
    a.id,
    a.employee_id,
    e.employee_code,
    e.biometric_user_id,
    a.attendance_date,
    a.punch_in_time,
    a.attendace_type,
    a.txn_id,
    a.records_source,
    a.created_at,
    c.company_name
FROM attendances a
LEFT JOIN employees e ON a.employee_id = e.id
LEFT JOIN companies c ON a.company_id = c.id
WHERE a.records_source = '3rd_party_api'
ORDER BY a.created_at DESC
LIMIT 50;

-- 2. Check Company Rate Limit Settings
SELECT 
    c.id,
    c.company_name,
    c.app_key,
    c.status,
    cd.attendance_request_rate_limit_per_minutes as rate_limit
FROM companies c
LEFT JOIN company_details cd ON c.id = cd.company_id
WHERE c.status = 'active'
ORDER BY c.id;

-- 3. Find Employee by biomax_id
SELECT 
    id,
    employee_code,
    biometric_user_id,
    company_id,
    full_name,
    status
FROM employees
WHERE biometric_user_id = 'EMP-019'  -- Replace with actual biomax_id
   OR employee_code = 'EMP-019';

-- 4. Count Attendance Records by Company (Last 24 Hours)
SELECT 
    c.company_name,
    COUNT(a.id) as total_records,
    SUM(CASE WHEN a.attendace_type = 'in' THEN 1 ELSE 0 END) as punch_in_count,
    SUM(CASE WHEN a.attendace_type = 'out' THEN 1 ELSE 0 END) as punch_out_count
FROM attendances a
JOIN companies c ON a.company_id = c.id
WHERE a.records_source = '3rd_party_api'
  AND a.created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
GROUP BY c.id, c.company_name
ORDER BY total_records DESC;

-- 5. Check for Duplicate Records
SELECT 
    a.employee_id,
    e.employee_code,
    a.attendance_date,
    a.punch_in_time,
    a.attendace_type,
    a.txn_id,
    COUNT(*) as duplicate_count
FROM attendances a
LEFT JOIN employees e ON a.employee_id = e.id
WHERE a.records_source = '3rd_party_api'
GROUP BY a.employee_id, a.attendance_date, a.punch_in_time, a.attendace_type, a.txn_id
HAVING duplicate_count > 1
ORDER BY duplicate_count DESC;

-- 6. Check Employees Missing biomax_id
SELECT 
    id,
    employee_code,
    full_name,
    company_id,
    biometric_user_id
FROM employees
WHERE (biometric_user_id IS NULL OR biometric_user_id = '')
  AND status = 'active'
ORDER BY company_id, employee_code;

-- 7. Check Failed/Skipped Records (from logs analysis)
-- Note: This requires log analysis, but you can check for employees that should have records
SELECT 
    e.id,
    e.employee_code,
    e.biometric_user_id,
    e.company_id,
    COUNT(a.id) as attendance_count
FROM employees e
LEFT JOIN attendances a ON e.id = a.employee_id 
    AND a.records_source = '3rd_party_api'
    AND a.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
WHERE e.biometric_user_id IS NOT NULL
  AND e.status = 'active'
GROUP BY e.id, e.employee_code, e.biometric_user_id, e.company_id
HAVING attendance_count = 0
ORDER BY e.company_id;

-- 8. Check Rate Limit Status (if using database cache)
-- Note: This is for reference, actual cache is in Redis/Memcached
SELECT 
    'Rate limit is stored in cache (Redis/Memcached), not database' as note;

-- 9. Verify Company App Key
SELECT 
    id,
    company_name,
    app_key,
    status,
    CASE 
        WHEN status = 'active' THEN 'OK'
        ELSE 'INACTIVE'
    END as status_check
FROM companies
WHERE app_key = 'Ocean@2025'  -- Replace with actual app_key
   OR id = 3;  -- Replace with company_id

-- 10. Check Attendance Records by Date Range
SELECT 
    DATE(a.attendance_date) as date,
    COUNT(*) as total_records,
    COUNT(DISTINCT a.employee_id) as unique_employees,
    SUM(CASE WHEN a.attendace_type = 'in' THEN 1 ELSE 0 END) as punch_ins,
    SUM(CASE WHEN a.attendace_type = 'out' THEN 1 ELSE 0 END) as punch_outs
FROM attendances a
WHERE a.records_source = '3rd_party_api'
  AND a.attendance_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
GROUP BY DATE(a.attendance_date)
ORDER BY date DESC;

-- 11. Check for Invalid Timestamps
SELECT 
    id,
    employee_id,
    attendance_date,
    punch_in_time,
    create_date,
    created_at,
    txn_id
FROM attendances
WHERE records_source = '3rd_party_api'
  AND (attendance_date IS NULL 
       OR punch_in_time IS NULL
       OR create_date IS NULL)
ORDER BY created_at DESC;

-- 12. Employee Attendance Summary
SELECT 
    e.employee_code,
    e.biometric_user_id,
    e.full_name,
    COUNT(a.id) as total_attendance,
    MIN(a.attendance_date) as first_attendance,
    MAX(a.attendance_date) as last_attendance
FROM employees e
LEFT JOIN attendances a ON e.id = a.employee_id 
    AND a.records_source = '3rd_party_api'
WHERE e.biometric_user_id = 'EMP-019'  -- Replace with actual biomax_id
GROUP BY e.id, e.employee_code, e.biometric_user_id, e.full_name;

-- 13. Check Transaction IDs (for duplicate detection)
SELECT 
    txn_id,
    COUNT(*) as count,
    GROUP_CONCAT(id) as attendance_ids
FROM attendances
WHERE records_source = '3rd_party_api'
  AND txn_id IS NOT NULL
GROUP BY txn_id
HAVING count > 1
ORDER BY count DESC;

-- 14. Company-wise Statistics
SELECT 
    c.id,
    c.company_name,
    COUNT(DISTINCT e.id) as total_employees_with_biomax_id,
    COUNT(DISTINCT a.employee_id) as employees_with_attendance,
    COUNT(a.id) as total_attendance_records,
    MAX(a.created_at) as last_attendance_received
FROM companies c
LEFT JOIN employees e ON c.id = e.company_id 
    AND e.biometric_user_id IS NOT NULL
    AND e.status = 'active'
LEFT JOIN attendances a ON c.id = a.company_id 
    AND a.records_source = '3rd_party_api'
    AND a.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
WHERE c.status = 'active'
GROUP BY c.id, c.company_name
ORDER BY total_attendance_records DESC;
