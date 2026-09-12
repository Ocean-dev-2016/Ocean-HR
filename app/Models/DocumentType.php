<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DocumentType extends Model
{
    use HasFactory, SoftDeletes;
    public $table = "document_types";

    protected $fillable = [
        'company_id',
        'name',
        'from_date',
        'to_date',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

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
}
