<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'name',
        'color',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * 取得員工所屬部門
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * 取得員工的所有班表記錄
     */
    public function scheduleRecords(): HasMany
    {
        return $this->hasMany(ScheduleRecord::class);
    }

    /**
     * 取得員工在特定班表的記錄
     */
    public function recordsForSchedule(int $scheduleId): HasMany
    {
        return $this->hasMany(ScheduleRecord::class)
            ->where('schedule_id', $scheduleId);
    }
}
