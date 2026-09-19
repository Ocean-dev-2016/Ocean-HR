<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OnboardingAsset extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'onboarding_assets';

    protected $fillable = [
        'onboarding_id',
        'employee_id',
        'assets_allocation_master_id',
        'asset_type',
        'asset_name',
        'asset_code_or_serial',
        'specification_or_size',
        'issued_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'issued_date' => 'date',
    ];

    public function onboarding()
    {
        return $this->belongsTo(Onboarding::class, 'onboarding_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function assetMaster()
    {
        return $this->belongsTo(AssetsAllocationMaster::class, 'assets_allocation_master_id');
    }
}
