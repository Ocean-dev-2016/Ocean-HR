<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AttendancePdfParser
{
    /**
     * Parse PDF file and extract attendance data
     * 
     * Note: This requires a PDF parsing library like smalot/pdfparser
     * Install: composer require smalot/pdfparser
     * 
     * @param string $filePath
     * @return array
     */
    public function parsePdf($filePath)
    {
        $attendanceData = [];
        
        try {
            // Check if PDF parser library is available
            if (!class_exists('\Smalot\PdfParser\Parser')) {
                throw new \Exception('PDF parser library not installed. Please run: composer require smalot/pdfparser');
            }

            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($filePath);
            $text = $pdf->getText();
            
            // Extract date from header (e.g., "Attendance Date- 18-Nov-2025")
            $attendanceDate = $this->extractDateFromHeader($text);
            
            // Parse table data
            $lines = explode("\n", $text);
            $dataRows = $this->parseTableRows($lines, $attendanceDate);
            
            $attendanceData = $dataRows;
            
        } catch (\Exception $e) {
            Log::error("PDF Parsing Error: " . $e->getMessage());
            throw $e;
        }
        
        return $attendanceData;
    }

    /**
     * Extract date from PDF header
     */
    private function extractDateFromHeader($text)
    {
        // Look for patterns like "Attendance Date- 18-Nov-2025" or "18-Nov-2025"
        $patterns = [
            '/Attendance\s+Date[-\s:]+(\d{1,2}[-/]\w+[-/]\d{4})/i',
            '/(\d{1,2}[-/]\w+[-/]\d{4})/',
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                try {
                    $dateStr = $matches[1];
                    // Try to parse date formats like "18-Nov-2025"
                    $date = Carbon::createFromFormat('d-M-Y', $dateStr);
                    return $date->format('Y-m-d');
                } catch (\Exception $e) {
                    continue;
                }
            }
        }
        
        // Default to today if not found
        return now()->format('Y-m-d');
    }

    /**
     * Parse table rows from PDF text
     */
    private function parseTableRows($lines, $defaultDate)
    {
        $rows = [];
        $inTable = false;
        $headerFound = false;
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Skip empty lines
            if (empty($line)) continue;
            
            // Detect table header
            if (stripos($line, 'Employee Code') !== false || stripos($line, 'S.No') !== false) {
                $headerFound = true;
                $inTable = true;
                continue;
            }
            
            // Skip if not in table
            if (!$inTable) continue;
            
            // Parse row data
            // Expected format: "11 | nikunj | GS | 07:37:42 | 00:00 | P |"
            $rowData = $this->parseRow($line, $defaultDate);
            if ($rowData) {
                $rows[] = $rowData;
            }
        }
        
        return $rows;
    }

    /**
     * Parse a single row from PDF text
     */
    private function parseRow($line, $defaultDate)
    {
        // Split by pipe or multiple spaces
        $parts = preg_split('/\s*\|\s*|\s{2,}/', $line);
        $parts = array_map('trim', $parts);
        $parts = array_filter($parts);
        $parts = array_values($parts);
        
        if (count($parts) < 3) {
            return null;
        }
        
        // Extract employee code (may be combined with S.No)
        $employeeCode = $this->extractEmployeeCode($parts[0] ?? '');
        $employeeName = $parts[1] ?? '';
        $shift = $parts[2] ?? '';
        $punchInTime = $this->parseTimeFromPdf($parts[3] ?? '');
        $punchOutTime = $this->parseTimeFromPdf($parts[4] ?? '');
        $status = strtoupper(trim($parts[5] ?? ''));
        $remark = $parts[6] ?? '';
        
        // Skip if absent
        if ($status === 'A' || $status === 'ABSENT') {
            return null;
        }
        
        // Skip if no times
        if (!$punchInTime && !$punchOutTime) {
            return null;
        }
        
        return [
            'biometric_user_id' => null,
            'employee_code' => $employeeCode,
            'employee_name' => $employeeName,
            'attendance_date' => $defaultDate,
            'punch_in_time' => $punchInTime,
            'punch_out_time' => $punchOutTime,
            'attendace_type' => $punchInTime ? 'in' : 'out',
            'shift' => $shift,
            'remark' => $remark,
            'status' => $status,
        ];
    }

    /**
     * Extract employee code (may be combined with S.No like "11" where 1 is S.No and 1 is code)
     */
    private function extractEmployeeCode($value)
    {
        $value = trim($value);
        
        // If it's just a number, return as is
        if (is_numeric($value)) {
            return $value;
        }
        
        // Try to extract code from combined format
        // For now, just return the value
        return $value;
    }

    /**
     * Parse time from PDF (format: "07:37:42" or "00:00")
     */
    private function parseTimeFromPdf($timeStr)
    {
        $timeStr = trim($timeStr);
        
        // Skip if "00:00" or empty
        if (empty($timeStr) || $timeStr === '00:00' || $timeStr === '00:00:00') {
            return null;
        }
        
        // Try to parse time
        try {
            $time = Carbon::createFromFormat('H:i:s', $timeStr);
            return $time->format('H:i:s');
        } catch (\Exception $e) {
            try {
                $time = Carbon::createFromFormat('H:i', $timeStr);
                return $time->format('H:i:s');
            } catch (\Exception $e2) {
                return null;
            }
        }
    }
}

