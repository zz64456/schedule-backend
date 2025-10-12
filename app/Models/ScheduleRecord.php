<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'schedule_id',
        'employee_id',
        'day',
        'is_off',
        'leave_type',
    ];

    protected $casts = [
        'is_off' => 'boolean',
    ];

    // 假別常數
    const LEAVE_TYPE_PERSONAL = 'personal'; // 事假
    const LEAVE_TYPE_SICK = 'sick';         // 病假

    /**
     * 取得班表記錄所屬的班表
     */
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    /**
     * 取得班表記錄所屬的員工
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * 切換休假狀態
     */
    public function toggleOff(): void
    {
        $this->update(['is_off' => !$this->is_off]);
    }
}
