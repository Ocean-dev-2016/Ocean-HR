<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyRegistration extends Model
{
    use SoftDeletes;

    protected $table = 'company_registrations';

    protected $fillable = [
        'gst_no',
        'company_name',
        'person_name',
        'whatsapp_number',
        'email',
        'password',
        'sp',
        'country_id',
        'state_id',
        'city_id',
        'plan_id',
        'date_format',
        'time_format',
        'hra_percentage',
        'otp',
        'status',
    ];

    public function plan()
    {
        return $this->belongsTo(PlanMaster::class, 'plan_id', 'id');
    }

    public function country()
    {
        return $this->belongsTo(MasterCountry::class, 'country_id', 'id');
    }

    public function state()
    {
        return $this->belongsTo(MasterState::class, 'state_id', 'id');
    }

    public function city()
    {
        return $this->belongsTo(MasterCity::class, 'city_id', 'id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'email', 'email');
    }
}
