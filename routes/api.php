<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ScheduleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// 認證相關
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/check', [AuthController::class, 'check']);
});

// 員工相關
Route::prefix('employees')->group(function () {
    Route::get('/', [EmployeeController::class, 'index']);
    Route::post('/{employee}/select', [EmployeeController::class, 'select']);
});

// 班表相關
Route::prefix('schedules')->group(function () {
    Route::get('/{year}/{month}', [ScheduleController::class, 'show']);
    Route::post('/records', [ScheduleController::class, 'updateRecord']);
    Route::post('/{schedule}/confirm', [ScheduleController::class, 'confirm']);
    Route::get('/{year}/{month}/export', [ScheduleController::class, 'export']);
});
