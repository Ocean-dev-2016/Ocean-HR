<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class CompanyDetails extends Model
{
    use SoftDeletes;

    protected $table = 'company_details';
    protected $fillable = [
        'company_id',
        'attendance_request_rate_limit_per_minutes',
        'color_bg_primary_1',
        'color_bg_primary_2',
        'color_bg_primary_3',
        'color_text_primary_1',
        'color_text_primary_2',
        'color_text_primary_3',
        'panel_sidebar_background_color',
        'panel_sidebar_text_color',
        'panel_text_color',
        'status_bar_color',
        'title_name_color',
        'all_icon_color',
        'edittext_title_color',
        'screen_background_light_color',
        'screen_background_dark_color',
        'all_screen_header_color',
        'all_screen_back_arrow_background_color',
        'all_screen_back_arrow_color',
        'data_list_border_color',
        'login_text_color_1',
        'login_text_color_2',
        'background_shape_1',
        'background_shape_2',
        'background_shape_3',
        'extra_color_1',
        'extra_color_2',
        'extra_color_3',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    protected static function generateRandomColor()
    {
        return '#' . substr(sha1(rand()), 0, 6); // Generates a random hex color code
    }

    public function getColorTextPrimary1Attribute($value)
    {
        return self::generateRandomColor();
        return $value ? $value : $this->generateRandomColor();
    }

    public function setColorBgPrimary1Attribute($value)
    {
        $this->attributes['color_bg_primary_1'] = $value ?: $this->generateRandomColor();
    }

    public function setColorBgPrimary2Attribute($value)
    {
        $this->attributes['color_bg_primary_2'] = $value ?: $this->generateRandomColor();
    }

    public function setColorBgPrimary3Attribute($value)
    {
        $this->attributes['color_bg_primary_3'] = $value ?: $this->generateRandomColor();
    }
}
