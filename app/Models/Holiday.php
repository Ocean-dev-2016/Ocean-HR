<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Holiday extends Model
{
    use SoftDeletes;

    protected $table = 'holidays';

    protected $fillable = [
        'holiday_label',
        'company_id',
        'country_id',
        'state_ids',
        'employee_designation_type',
        'from_date',
        'to_date',
        'remark',
        'status',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
        'deleted_by',
        'deleted_at'
    ];

    public static $employee_designation_type_arr = [
        '' => 'Select....',
        'both' => 'Both',
        'employee' => 'Employee',
        'worker' => 'Worker',
    ];

    // 🔹 Accessor → when fetching from DB → show as d-m-Y
    public function getFromDateAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d-m-Y') : null;
    }

    // 🔹 Mutator → when saving to DB → convert d-m-Y → Y-m-d
    public function setFromDateAttribute($value)
    {
        $this->attributes['from_date'] = $value
            ? Carbon::createFromFormat('d-m-Y', $value)->format('Y-m-d')
            : null;
    }
    // 🔹 Accessor → when fetching from DB → show as d-m-Y
    public function getToDateAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d-m-Y') : null;
    }

    // 🔹 Mutator → when saving to DB → convert d-m-Y → Y-m-d
    public function setToDateAttribute($value)
    {
        $this->attributes['to_date'] = $value
            ? Carbon::createFromFormat('d-m-Y', $value)->format('Y-m-d')
            : null;
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    /**
     * Accessor & Mutator for state_ids
     */
    protected function stateIds(): Attribute
    {
        return Attribute::make(
            // When getting → convert "1,2,3" → [1,2,3]
            get: fn($value) => $value ? explode(',', $value) : [],
            // When setting → convert ["1","2","3"] → "1,2,3"
            set: fn($value) => is_array($value) ? implode(',', $value) : $value,
        );
    }

    public function getStateNamesAttribute(){
        ## state_names
        if($this->state_ids){
            $states = MasterState::whereIn('id',$this->state_ids)->get();
            if($states->count()){
                return $states->pluck("name")->implode(', ');
                return implode(", ", $states->pluck("name"));
            }
        }
        return '--';
    }
}
