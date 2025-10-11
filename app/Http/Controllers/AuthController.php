<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Admin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    /**
     * 管理員登入
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $admin = Admin::where('username', $request->username)->first();

        if (!$admin || !$admin->checkPassword($request->password)) {
            return response()->json([
                'success' => false,
                'message' => '帳號或密碼錯誤',
            ], 401);
        }

        // 儲存管理員資訊到 session
        Session::put('admin_id', $admin->id);
        Session::put('admin_name', $admin->name);

        // 記錄登入日誌
        ActivityLog::record(
            ActivityLog::ACTION_ADMIN_LOGIN,
            ActivityLog::USER_TYPE_ADMIN,
            $admin->id,
            null,
            null,
            ['username' => $admin->username]
        );

        return response()->json([
            'success' => true,
            'message' => '登入成功',
            'admin' => [
                'id' => $admin->id,
                'name' => $admin->name,
                'username' => $admin->username,
            ],
        ]);
    }

    /**
     * 管理員登出
     */
    public function logout(Request $request): JsonResponse
    {
        $adminId = Session::get('admin_id');

        // 記錄登出日誌
        if ($adminId) {
            ActivityLog::record(
                ActivityLog::ACTION_ADMIN_LOGOUT,
                ActivityLog::USER_TYPE_ADMIN,
                $adminId
            );
        }

        Session::forget('admin_id');
        Session::forget('admin_name');

        return response()->json([
            'success' => true,
            'message' => '登出成功',
        ]);
    }

    /**
     * 檢查登入狀態
     */
    public function check(Request $request): JsonResponse
    {
        $adminId = Session::get('admin_id');

        if ($adminId) {
            $admin = Admin::find($adminId);
            return response()->json([
                'isAuthenticated' => true,
                'admin' => [
                    'id' => $admin->id,
                    'name' => $admin->name,
                    'username' => $admin->username,
                ],
            ]);
        }

        return response()->json([
            'isAuthenticated' => false,
        ]);
    }
}
