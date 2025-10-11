<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sort_order',
    ];

    /**
     * 取得部門下的所有員工
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class)->orderBy('sort_order');
    }
}
