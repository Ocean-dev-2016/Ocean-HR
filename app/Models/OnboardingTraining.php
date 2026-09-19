<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OnboardingTraining extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'onboarding_trainings';

    protected $fillable = [
        'onboarding_id',
        'module_number',
        'title',
        'description',
        'pdf_file_path',
        'pdf_file_name',
        'external_url',
        'status',
        'completed_at',
        'trainer_notes',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public function onboarding()
    {
        return $this->belongsTo(Onboarding::class, 'onboarding_id');
    }

    public function getPdfUrlAttribute(): ?string
    {
        if ($this->pdf_file_path) {
            return asset('storage/' . $this->pdf_file_path);
        }
        return null;
    }
}
