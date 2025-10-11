<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Department;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    /**
     * 新增部門
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:departments,name',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        // 如果沒提供 sort_order，自動設為最後
        if (!isset($validated['sort_order'])) {
            $validated['sort_order'] = (Department::max('sort_order') ?? 0) + 1;
        }

        $department = Department::create($validated);

        // 記錄操作日誌
        ActivityLog::record(
            'department_created',
            ActivityLog::USER_TYPE_ADMIN,
            session('admin_id'),
            null,
            null,
            ['department_name' => $department->name]
        );

        return response()->json([
            'success' => true,
            'department' => $department,
        ]);
    }
}
