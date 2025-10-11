<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admins = [
            [
                'username' => 'admin',
                'password' => 'password', // 會自動 hash
                'name' => '系統管理員',
            ],
            [
                'username' => 'manager',
                'password' => 'password',
                'name' => '小組長',
            ],
        ];

        foreach ($admins as $admin) {
            Admin::create($admin);
        }
    }
}
