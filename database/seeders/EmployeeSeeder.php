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
            // 正職員工
            [
                'department_id' => $fullTimeDept->id,
                'name' => 'IVY',
                'color' => '#FF6B6B',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'department_id' => $fullTimeDept->id,
                'name' => '孟孟',
                'color' => '#4ECDC4',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'department_id' => $fullTimeDept->id,
                'name' => '小美',
                'color' => '#95E1D3',
                'is_active' => true,
                'sort_order' => 3,
            ],

            // 兼職員工
            [
                'department_id' => $partTimeDept->id,
                'name' => '小華',
                'color' => '#FFE66D',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'department_id' => $partTimeDept->id,
                'name' => '小明',
                'color' => '#A8DADC',
                'is_active' => true,
                'sort_order' => 2,
            ],
        ];

        foreach ($employees as $employee) {
            Employee::create($employee);
        }
    }
}
