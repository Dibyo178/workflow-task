<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AuditLogController;
use App\Http\Controllers\Api\TaskController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes — No token required
|--------------------------------------------------------------------------
*/
Route::post('register', [AuthController::class, 'register']);
Route::post('login',    [AuthController::class, 'login'])->name('login');

/*
|--------------------------------------------------------------------------
| Protected Routes — JWT token required
|--------------------------------------------------------------------------
*/
Route::middleware('auth:api')->group(function () {

    // Auth
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me',      [AuthController::class, 'me']);

    // Tasks — USER workflow endpoints
    Route::apiResource('tasks', TaskController::class);
    Route::patch('tasks/{task}/start',    [TaskController::class, 'start']);
    Route::patch('tasks/{task}/complete', [TaskController::class, 'complete']);

    /*
    |----------------------------------------------------------------------
    | Admin Only Routes — JWT + ADMIN role required
    |----------------------------------------------------------------------
    */
    Route::middleware('admin')->group(function () {

        // Task approval workflow (ADMIN only)
        Route::patch('tasks/{task}/approve', [TaskController::class, 'approve']);
        Route::patch('tasks/{task}/reject',  [TaskController::class, 'reject']);

        // User management (ADMIN only)
        Route::get('users',                      [AuthController::class, 'allUsers']);
        Route::patch('users/{user}/status',      [AuthController::class, 'updateStatus']);

        // Audit logs (ADMIN only)
        Route::get('audit-logs', [AuditLogController::class, 'index']);
    });
});
