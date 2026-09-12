<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttendanceImportFile extends Model
{
    use SoftDeletes;

    protected $table = 'attendance_import_files';
    
    protected $fillable = [
        'company_id',
        'filename',
        'file_type',
        'status',
        'total_rows',
        'total_success',
        'total_failed',
        'total_duplicates',
        'errors',
        'processing_log',
        'started_at',
        'completed_at',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }
}

