<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use SoftDeletes;

    protected $table = 'expenses';

    static $folderPath = "expenses/";

    protected $fillable = [
        'company_id',
        'branch_id',
        'team_person_id',
        'expense_category_id',
        'expense_subcategory_id',
        'date',
        'req_amount',
        'pass_amount',
        'status',
        'reason',
        'attachment',
        'remark',
        'created_by',
        'updated_by',
        'deleted_by',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $casts = [
        'date' => 'date',
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
     * Relationship with Employee (Team Person)
     */
    public function employees()
    {
        return $this->belongsTo(Employee::class, 'team_person_id', 'id');
    }

    /**
     * Relationship with Expense Category
     */
    public function expense_category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id', 'id');
    }

    /**
     * Relationship with Expense SubCategory
     */
    public function expense_sub_category()
    {
        return $this->belongsTo(ExpenseSubCategory::class, 'expense_subcategory_id', 'id');
    }

    /**
     * Get attachment URL
     */
    public function getAttachmentUrlAttribute()
    {
        if ($this->attachment) {
            if (file_exists(public_path($this->attachment))) {
                return asset($this->attachment);
            }
        }
        return null;
    }
}
