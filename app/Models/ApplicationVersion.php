<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ApplicationVersion extends Model
{
    use HasFactory;

    public $table = "application_version";

    protected $fillable = [
        'version',
        'apk_file',
        'is_force_update',
        'update_message',
    ];

    protected $appends = ['apk_file_url'];

    public function getApkFileUrlAttribute()
    {
        # apk_file_url
        if ($this->apk_file) {
            if (file_exists(public_path($this->apk_file))) {
                return asset($this->apk_file);
            }
            return public_path($this->apk_file);
        }
        return null;
    }

}
