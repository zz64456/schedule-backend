<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    /**
     * 取得所有員工（按部門分組）
     */
    public function index(): JsonResponse
    {
        $departments = Department::with(['employees' => function ($query) {
            $query->where('is_active', true)->orderBy('sort_order');
        }])->orderBy('sort_order')->get();

        return response()->json([
            'success' => true,
            'departments' => $departments,
        ]);
    }

    /**
     * 記錄員工被選擇
     */
    public function select(Request $request, Employee $employee): JsonResponse
    {
        $request->validate([
            'schedule_id' => 'nullable|exists:schedules,id',
        ]);

        // 記錄選擇員工的日誌
        ActivityLog::record(
            ActivityLog::ACTION_EMPLOYEE_SELECTED,
            ActivityLog::USER_TYPE_GUEST,
            null,
            $employee->id,
            $request->schedule_id,
            [
                'employee_name' => $employee->name,
                'employee_color' => $employee->color,
            ]
        );

        return response()->json([
            'success' => true,
            'employee' => $employee,
        ]);
    }
}
