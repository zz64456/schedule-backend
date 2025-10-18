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

    /**
     * 取得可用的員工代表色
     */
    public function getAvailableColors(): JsonResponse
    {
        $allColors = config('employee_colors');
        $usedColors = Employee::pluck('color')->toArray();

        $availableColors = array_filter($allColors, function($color) use ($usedColors) {
            return !in_array($color['hex'], $usedColors);
        });

        return response()->json([
            'success' => true,
            'colors' => array_values($availableColors),
        ]);
    }

    /**
     * 新增員工
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'department_id' => 'required|exists:departments,id',
            'color' => 'required|regex:/^#[0-9A-F]{6}$/i',
        ]);

        // 驗證顏色未被使用
        if (Employee::where('color', $validated['color'])->exists()) {
            return response()->json([
                'success' => false,
                'message' => '此顏色已被使用，請選擇其他顏色',
            ], 400);
        }

        // 取得該部門最大 sort_order
        $maxSortOrder = Employee::where('department_id', $validated['department_id'])
            ->max('sort_order');

        $employee = Employee::create([
            'name' => $validated['name'],
            'department_id' => $validated['department_id'],
            'color' => $validated['color'],
            'sort_order' => ($maxSortOrder ?? 0) + 1,
            'is_active' => true,
        ]);

        // 記錄操作日誌
        ActivityLog::record(
            'employee_created',
            ActivityLog::USER_TYPE_ADMIN,
            session('admin_id'),
            $employee->id,
            null,
            ['employee_name' => $employee->name, 'color' => $employee->color]
        );

        return response()->json([
            'success' => true,
            'employee' => $employee->load('department'),
        ]);
    }

    /**
     * 刪除員工（軟刪除）
     */
    public function destroy(Employee $employee): JsonResponse
    {
        // 軟刪除：將 is_active 設為 false
        $employee->is_active = false;
        $employee->save();

        // 記錄操作日誌
        ActivityLog::record(
            'employee_deleted',
            ActivityLog::USER_TYPE_ADMIN,
            session('admin_id'),
            $employee->id,
            null,
            ['employee_name' => $employee->name]
        );

        return response()->json([
            'success' => true,
            'message' => '員工已刪除',
        ]);
    }
}
