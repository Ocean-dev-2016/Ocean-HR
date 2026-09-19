<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OnboardingDocument extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'onboarding_documents';

    protected $fillable = [
        'onboarding_id',
        'employee_id',
        'document_type',
        'document_title',
        'document_number',
        'file_path',
        'file_name',
        'status',
        'verified_by',
        'verified_at',
        'rejection_reason',
        'remarks',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    public function onboarding()
    {
        return $this->belongsTo(Onboarding::class, 'onboarding_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function getFileUrlAttribute(): ?string
    {
        if ($this->file_path) {
            return asset('storage/' . $this->file_path);
        }
        return null;
    }
}
