<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Expense extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'description',
        'date',
        'expense_category_id',
        'expense_sub_category_id',
        'note',
        'debit',
        'credit'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function expenseCategory() : HasOne
    {
        return $this->hasOne(ExpenseCategory::class, 'id', 'expense_category_id');
    }

    public function expenseSubCategory()
    {
        return $this->hasOne(ExpenseSubCategory::class, 'id', 'expense_sub_category_id');
    }

    public function scopeQueryUser(Builder $query, int $userId) : Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeQueryCategory(Builder $query, int $categoryId) : Builder
    {
        return $query->where('expense_category_id', $categoryId);
    }
}
