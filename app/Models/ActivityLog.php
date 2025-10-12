<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'action_type',
        'user_type',
        'user_id',
        'employee_id',
        'schedule_id',
        'ip_address',
        'user_agent',
        'details',
    ];

    protected $casts = [
        'details' => 'array',
    ];

    /**
     * 操作類型常數
     */
    const ACTION_EMPLOYEE_SELECTED = 'employee_selected';
    const ACTION_SCHEDULE_UPDATED = 'schedule_updated';
    const ACTION_ADMIN_LOGIN = 'admin_login';
    const ACTION_SCHEDULE_CONFIRMED = 'schedule_confirmed';
    const ACTION_SCHEDULE_UNCONFIRMED = 'schedule_unconfirmed';
    const ACTION_SCHEDULE_EXPORTED = 'schedule_exported';
    const ACTION_ADMIN_LOGOUT = 'admin_logout';

    /**
     * 使用者類型常數
     */
    const USER_TYPE_GUEST = 'guest';
    const USER_TYPE_ADMIN = 'admin';

    /**
     * 取得關聯的員工
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * 取得關聯的班表
     */
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    /**
     * 取得關聯的管理員
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'user_id');
    }

    /**
     * 記錄活動日誌的靜態方法
     */
    public static function record(
        string $actionType,
        string $userType = self::USER_TYPE_GUEST,
        ?int $userId = null,
        ?int $employeeId = null,
        ?int $scheduleId = null,
        ?array $details = null
    ): self {
        return self::create([
            'action_type' => $actionType,
            'user_type' => $userType,
            'user_id' => $userId,
            'employee_id' => $employeeId,
            'schedule_id' => $scheduleId,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'details' => $details,
        ]);
    }
}
