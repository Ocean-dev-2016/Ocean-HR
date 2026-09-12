<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExpenseSubCategory extends Model
{
    use SoftDeletes;

    protected $table = 'expense_sub_categories';

    public static $expense_type = [
        'General' => 'General',
        'KM' => 'KM',
        'Food' => 'Food',
    ];

    protected $fillable = [
        'company_id',
        'branch_id',
        'expense_category_id',
        'name',
        'expense_type',
        'team_person_ids',
        'min_amount',
        'max_amount',
        'per_km_rate',
        'fix_amount',
        'from_time',
        'to_time',
        'is_image_required',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * Relationship with Company
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    /**
     * Relationship with Branch
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

    /**
     * Relationship with Expense Category
     */
    public function expense_category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id', 'id');
    }

    /**
     * Get employees (Team Persons) - Accessor that works like a relationship
     * The controller uses: $row->employees->pluck('name')
     */
    public function getEmployeesAttribute()
    {
        $ids = $this->attributes['team_person_ids'] ?? '';
        if (empty($ids)) {
            return collect([]);
        }
        $idArray = explode(',', $ids);
        return Employee::whereIn('id', $idArray)->get();
    }

    /**
     * Set team person IDs from array
     */
    public function setTeamPersonIdsAttribute($value)
    {
        $this->attributes['team_person_ids'] = is_array($value) ? implode(',', $value) : $value;
    }
}
