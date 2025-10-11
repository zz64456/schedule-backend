<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Employee;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fullTimeDept = Department::where('name', '正職')->first();
        $partTimeDept = Department::where('name', '兼職')->first();

        $employees = [
            // 正職員工 - 使用不同色系確保唯一性
            [
                'department_id' => $fullTimeDept->id,
                'name' => 'IVY',
                'color' => '#EF4444', // 紅色系 (Tailwind Red-500)
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'department_id' => $fullTimeDept->id,
                'name' => '孟孟',
                'color' => '#10B981', // 綠色系 (Tailwind Green-500)
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'department_id' => $fullTimeDept->id,
                'name' => '小美',
                'color' => '#8B5CF6', // 紫色系 (Tailwind Purple-500)
                'is_active' => true,
                'sort_order' => 3,
            ],

            // 兼職員工 - 使用完全不同的色系
            [
                'department_id' => $partTimeDept->id,
                'name' => '小華',
                'color' => '#F59E0B', // 琥珀色系 (Tailwind Amber-500)
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'department_id' => $partTimeDept->id,
                'name' => '小明',
                'color' => '#3B82F6', // 藍色系 (Tailwind Blue-500)
                'is_active' => true,
                'sort_order' => 2,
            ],
        ];

        foreach ($employees as $employee) {
            Employee::create($employee);
        }
    }
}
