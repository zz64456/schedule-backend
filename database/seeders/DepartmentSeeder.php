<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            ['name' => '正職', 'sort_order' => 1],
            ['name' => '兼職', 'sort_order' => 2],
        ];

        foreach ($departments as $department) {
            Department::create($department);
        }
    }
}
