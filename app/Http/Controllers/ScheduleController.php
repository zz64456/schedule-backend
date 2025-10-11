<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Employee;
use App\Models\Schedule;
use App\Models\ScheduleRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ScheduleController extends Controller
{
    /**
     * 取得或建立指定年月的班表
     */
    public function show(Request $request, int $year, int $month): JsonResponse
    {
        $schedule = Schedule::firstOrCreate(
            ['year' => $year, 'month' => $month],
            ['is_confirmed' => false]
        );

        // 載入所有員工和他們的班表記錄
        $employees = Employee::where('is_active', true)
            ->with(['department', 'scheduleRecords' => function ($query) use ($schedule) {
                $query->where('schedule_id', $schedule->id);
            }])
            ->get()
            ->groupBy('department_id');

        return response()->json([
            'success' => true,
            'schedule' => $schedule,
            'employees' => $employees,
        ]);
    }

    /**
     * 更新班表記錄（切換休假狀態）
     */
    public function updateRecord(Request $request): JsonResponse
    {
        $request->validate([
            'schedule_id' => 'required|exists:schedules,id',
            'employee_id' => 'required|exists:employees,id',
            'day' => 'required|integer|min:1|max:31',
        ]);

        $schedule = Schedule::findOrFail($request->schedule_id);

        // 檢查班表是否已確認（只有管理員可以編輯已確認的班表）
        if ($schedule->is_confirmed && !Session::has('admin_id')) {
            return response()->json([
                'success' => false,
                'message' => '班表已確認，只有管理員可以編輯',
            ], 403);
        }

        // 取得或建立記錄
        $record = ScheduleRecord::firstOrCreate(
            [
                'schedule_id' => $request->schedule_id,
                'employee_id' => $request->employee_id,
                'day' => $request->day,
            ],
            ['is_off' => false]
        );

        // 切換休假狀態
        $record->toggleOff();

        // 記錄操作日誌
        $adminId = Session::get('admin_id');
        ActivityLog::record(
            ActivityLog::ACTION_SCHEDULE_UPDATED,
            $adminId ? ActivityLog::USER_TYPE_ADMIN : ActivityLog::USER_TYPE_GUEST,
            $adminId,
            $request->employee_id,
            $request->schedule_id,
            [
                'day' => $request->day,
                'is_off' => $record->is_off,
            ]
        );

        return response()->json([
            'success' => true,
            'record' => $record,
        ]);
    }

    /**
     * 確認班表（管理員專用）
     */
    public function confirm(Request $request, Schedule $schedule): JsonResponse
    {
        $adminId = Session::get('admin_id');

        if (!$adminId) {
            return response()->json([
                'success' => false,
                'message' => '需要管理員權限',
            ], 403);
        }

        if ($schedule->is_confirmed) {
            return response()->json([
                'success' => false,
                'message' => '班表已經確認過了',
            ], 400);
        }

        $schedule->confirm($adminId);

        // 記錄操作日誌
        ActivityLog::record(
            ActivityLog::ACTION_SCHEDULE_CONFIRMED,
            ActivityLog::USER_TYPE_ADMIN,
            $adminId,
            null,
            $schedule->id,
            [
                'year' => $schedule->year,
                'month' => $schedule->month,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => '班表已確認',
            'schedule' => $schedule,
        ]);
    }

    /**
     * 匯出班表為 Excel（管理員專用）
     */
    public function export(Request $request, int $year, int $month)
    {
        $adminId = Session::get('admin_id');

        if (!$adminId) {
            return response()->json([
                'success' => false,
                'message' => '需要管理員權限',
            ], 403);
        }

        $schedule = Schedule::where('year', $year)
            ->where('month', $month)
            ->first();

        if (!$schedule) {
            return response()->json([
                'success' => false,
                'message' => '找不到該月份的班表',
            ], 404);
        }

        // 記錄操作日誌
        ActivityLog::record(
            ActivityLog::ACTION_SCHEDULE_EXPORTED,
            ActivityLog::USER_TYPE_ADMIN,
            $adminId,
            null,
            $schedule->id,
            [
                'year' => $year,
                'month' => $month,
            ]
        );

        // 建立 Excel
        $spreadsheet = $this->createExcelSchedule($schedule, $year, $month);

        // 輸出檔案
        $writer = new Xlsx($spreadsheet);
        $filename = "班表_{$year}年{$month}月.xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment;filename=\"{$filename}\"");
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    /**
     * 建立 Excel 班表
     */
    private function createExcelSchedule(Schedule $schedule, int $year, int $month): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // 計算該月份的天數
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year + 1911); // 轉換為西元年

        // A1: 年月標題
        $sheet->setCellValue('A1', "{$year}.{$month}");

        // A2: 空白
        $sheet->setCellValue('A2', '');

        // 設定日期列 (B1, C1, D1...)
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $col = chr(66 + $day - 1); // B=66
            if ($day > 26) {
                $col = 'A' . chr(65 + $day - 27);
            }

            // 日期
            $sheet->setCellValue("{$col}1", $day);

            // 星期
            $timestamp = mktime(0, 0, 0, $month, $day, $year + 1911);
            $dayOfWeek = ['日', '一', '二', '三', '四', '五', '六'][date('w', $timestamp)];
            $sheet->setCellValue("{$col}2", $dayOfWeek);

            // 星期日塗橘色（店休）
            if ($dayOfWeek === '日') {
                for ($row = 2; $row <= 100; $row++) {
                    $sheet->getStyle("{$col}{$row}")->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('FFFFA500');
                }
            }
        }

        // 載入員工資料
        $employees = Employee::where('is_active', true)
            ->with(['department', 'scheduleRecords' => function ($query) use ($schedule) {
                $query->where('schedule_id', $schedule->id);
            }])
            ->orderBy('department_id')
            ->orderBy('sort_order')
            ->get();

        $currentRow = 3;
        $currentDeptId = null;

        foreach ($employees as $employee) {
            // 如果換部門，先插入部門名稱
            if ($currentDeptId !== $employee->department_id) {
                $sheet->setCellValue("A{$currentRow}", $employee->department->name);
                $currentRow++;
                $currentDeptId = $employee->department_id;
            }

            // 員工名稱
            $sheet->setCellValue("A{$currentRow}", $employee->name);

            // 填入休假日
            $offDays = $employee->scheduleRecords->where('is_off', true)->pluck('day')->toArray();
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $col = chr(66 + $day - 1);
                if ($day > 26) {
                    $col = 'A' . chr(65 + $day - 27);
                }

                if (in_array($day, $offDays)) {
                    $sheet->getStyle("{$col}{$currentRow}")->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('FFFF0000'); // 紅色
                }
            }

            $currentRow++;
        }

        return $spreadsheet;
    }
}
