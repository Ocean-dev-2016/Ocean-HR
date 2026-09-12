<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeImportFile extends Model
{
    protected $table = 'employee_import_files';
    protected $fillable = [
        'company_id',
        'filename',
        'total_employees',
        'total_success_employees',
        'total_failed_employees',
        'errors',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
}
