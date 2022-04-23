<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

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
        'amount'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->hasOne(ExpenseCategory::class, 'id', 'expense_category_id');
    }

    public function subCategory()
    {
        return $this->hasOne(ExpenseSubCategory::class, 'id', 'expense_sub_category_id');
    }

    public function scopeQueryUser(Builder $query, int $userId) : Builder
    {
        return $query->where('user_id', $userId);
    }
}
