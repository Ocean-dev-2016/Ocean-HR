<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Company extends Model
{
    use SoftDeletes;

    protected $table = 'companies';

    public static $folderPath = 'companies/';

    protected $appends = ['company_logo_url', 'company_favicon_url', 'white_labeling_logo_url', 'app_logo_url', 'order_header_logo_url', 'order_footer_logo_url'];

    protected $fillable = [
        'gst_no',
        'company_name',
        'person_name',
        'whatsapp_number',
        'email',
        'password',
        'sp',
        'otp',
        'pan_card',
        'bank_details',
        'address',
        'country_id',
        'state_id',
        'city_id',
        'plan_id',
        'app_right',
        'panel_right',
        'plan_from',
        'plan_to',
        'app_key',
        'panel_url',
        'database_name',
        'database_user',
        'database_password',
        'max_employee_user_count',
        'mobile_min',
        'mobile_max',
        'hra_percentage',
        'employee_code_auto_generation',
        'date_format',
        'time_format',
        'branch_type',
        'company_logo',
        'company_favicon',
        'white_labeling_logo',
        'app_logo',
        'order_header_logo',
        'order_footer_logo',
        'is_copyright_view',
        'default_password',
        'reset_password',
        'phonecode',
        'status',
        'register_type',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function company_details()
    {
        return $this->hasOne(CompanyDetails::class, 'company_id', 'id');
    }

    // Get value as array (accessor)
    public function getAppRightAttribute($value)
    {
        return $value ? explode(',', $value) : [];
    }

    // Store value as comma-separated string (mutator)
    public function setAppRightAttribute($value)
    {
        $this->attributes['app_right'] = is_array($value) ? implode(',', $value) : $value;
    }

    // Get value as array (accessor)
    public function getPanelRightAttribute($value)
    {
        return $value ? explode(',', $value) : [];
    }

    // Store value as comma-separated string (mutator)
    public function setPanelRightAttribute($value)
    {
        $this->attributes['panel_right'] = is_array($value) ? implode(',', $value) : $value;
    }

    // Automatically generate the app_key
    public function setAppKeyAttribute($value)
    {
        if (! $value) {
            $this->attributes['app_key'] = Str::random(rand(5, 10)); // Generate a random app key (alphanumeric, length between 5 to 10)
        } else {
            $this->attributes['app_key'] = $value;
        }
    }

    public function getCurrentLatestPlanAttribute()
    {
        // current_latest_plan
        return CompanySubscriptionPlan::where('company_id', $this->id)->where('plan_id', $this->plan_id)->orderBy('id', 'desc')->first();
    }

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

    public function getCompanyLogoUrlAttribute()
    {
        // company_logo_url
        if ($this->company_logo) {
            if (file_exists(public_path($this->company_logo))) {
                return asset($this->company_logo);
            }
        }

        return null;
    }

    public function getCompanyFaviconUrlAttribute()
    {
        // company_favicon_url
        if ($this->company_favicon) {
            if (file_exists(public_path($this->company_favicon))) {
                return asset($this->company_favicon);
            }
        }

        return null;
    }

    public function getWhiteLabelingLogoUrlAttribute()
    {
        // white_labeling_logo_url
        if ($this->white_labeling_logo) {
            if (file_exists(public_path($this->white_labeling_logo))) {
                return asset($this->white_labeling_logo);
            }
        }

        return null;
    }

    public function getAppLogoUrlAttribute()
    {
        // app_logo_url
        if ($this->app_logo) {
            if (file_exists(public_path($this->app_logo))) {
                return asset($this->app_logo);
            }
        }

        return null;
    }

    public function getOrderHeaderLogoUrlAttribute()
    {
        // order_header_logo_url
        if ($this->order_header_logo) {
            if (file_exists(public_path($this->order_header_logo))) {
                return asset($this->order_header_logo);
            }
        }

        return null;
    }

    public function getOrderFooterLogoUrlAttribute()
    {
        // order_footer_logo_url
        if ($this->order_footer_logo) {
            if (file_exists(public_path($this->order_footer_logo))) {
                return asset($this->order_footer_logo);
            }
        }

        return null;
    }

    public function MailSetting()
    {
        return $this->belongsTo(MailSetting::class, 'id', 'company_id');
    }
}
