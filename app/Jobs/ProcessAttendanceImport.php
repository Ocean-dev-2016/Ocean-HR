<?php

namespace App\Jobs;

use App\Imports\AttendanceImport;
use App\Models\AttendanceImportFile;
use App\Services\AttendancePdfParser;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ProcessAttendanceImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $importFileId;
    protected $filePath;
    protected $companyId;
    protected $userId;
    protected $fileType;
    protected $authLoginUserDetail;

    /**
     * Create a new job instance.
     */
    public function __construct($importFileId, $filePath, $companyId, $userId, $fileType, $authLoginUserDetail)
    {
        $this->importFileId = $importFileId;
        $this->filePath = $filePath;
        $this->companyId = $companyId;
        $this->userId = $userId;
        $this->fileType = $fileType;
        $this->authLoginUserDetail = $authLoginUserDetail;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Update status to processing
            $importFile = AttendanceImportFile::find($this->importFileId);
            if (!$importFile) {
                Log::error("Attendance import file not found: {$this->importFileId}");
                return;
            }

            $importFile->update([
                'status' => 'processing',
                'started_at' => now(),
            ]);

            // Process based on file type
            if ($this->fileType === 'pdf') {
                $this->processPdf();
            } else {
                $this->processExcel();
            }

            // Update status to completed
            $importFile->refresh();
            $importFile->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            Log::info("Attendance import job completed: {$this->importFileId}");

        } catch (\Exception $e) {
            Log::error("Attendance import job failed: " . $e->getMessage());
            
            $importFile = AttendanceImportFile::find($this->importFileId);
            if ($importFile) {
                $importFile->update([
                    'status' => 'failed',
                    'completed_at' => now(),
                    'errors' => json_encode(['error' => $e->getMessage()]),
                ]);
            }
            
            throw $e;
        }
    }

    /**
     * Process Excel file
     */
    private function processExcel()
    {
        $fullPath = Storage::disk('public')->path($this->filePath);
        
        Excel::import(
            new AttendanceImport($this->companyId, $this->userId, $this->importFileId, $this->authLoginUserDetail),
            $fullPath
        );
    }

    /**
     * Process PDF file
     */
    private function processPdf()
    {
        $pdfParser = new AttendancePdfParser();
        $fullPath = Storage::disk('public')->path($this->filePath);
        
        // Parse PDF
        $attendanceData = $pdfParser->parsePdf($fullPath);
        
        // Convert array to Collection with proper structure for AttendanceImport
        $collection = collect($attendanceData)->map(function ($row) {
            return collect($row);
        });
        
        // Use AttendanceImport to process the data
        $import = new AttendanceImport($this->companyId, $this->userId, $this->importFileId, $this->authLoginUserDetail);
        $import->collection($collection);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        $importFile = AttendanceImportFile::find($this->importFileId);
        if ($importFile) {
            $importFile->update([
                'status' => 'failed',
                'completed_at' => now(),
                'errors' => json_encode([
                    'error' => $exception->getMessage(),
                    'trace' => $exception->getTraceAsString(),
                ]),
            ]);
        }
        
        Log::error("Attendance import job failed: {$this->importFileId}", [
            'exception' => $exception->getMessage(),
        ]);
    }
}

