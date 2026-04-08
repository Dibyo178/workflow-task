<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TaskController;
use Illuminate\Support\Facades\Route;

// 1. Public Routes (Token charai access kora jabe)
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login'])->name('login');

// 2. Protected Routes (Oboshoy JWT Token lagbe)
Route::middleware('auth:api')->group(function () {

    // Task CRUD (Index, Store, Show, Update, Delete)
    Route::apiResource('tasks', TaskController::class);

    // 3. Admin Only Routes (Role-Based Access Control)
    Route::middleware('admin')->group(function () {
        // Task Approval Logic
        Route::post('tasks/{task}/approve', [TaskController::class, 'approve']);

        // Admin can see all users
        Route::get('users', [AuthController::class, 'allUsers']);
    });
});
