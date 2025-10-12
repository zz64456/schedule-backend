<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'year',
        'month',
        'is_confirmed',
        'confirmed_at',
        'confirmed_by',
    ];

    protected $casts = [
        'is_confirmed' => 'boolean',
        'confirmed_at' => 'datetime',
    ];

    /**
     * 取得確認班表的管理員
     */
    public function confirmedByAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'confirmed_by');
    }

    /**
     * 取得班表的所有記錄
     */
    public function records(): HasMany
    {
        return $this->hasMany(ScheduleRecord::class);
    }

    /**
     * 取得特定員工在此班表的記錄
     */
    public function recordsForEmployee(int $employeeId): HasMany
    {
        return $this->hasMany(ScheduleRecord::class)
            ->where('employee_id', $employeeId);
    }

    /**
     * 確認班表
     */
    public function confirm(int $adminId): void
    {
        $this->update([
            'is_confirmed' => true,
            'confirmed_at' => now(),
            'confirmed_by' => $adminId,
        ]);
    }

    /**
     * 取消確認班表
     */
    public function unconfirm(): void
    {
        $this->update([
            'is_confirmed' => false,
            'confirmed_at' => null,
            'confirmed_by' => null,
        ]);
    }
}
