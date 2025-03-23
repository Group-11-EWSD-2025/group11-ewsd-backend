<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ConfigController;
use Illuminate\Support\Facades\Route;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('reset-password', [AuthController::class, 'forgotPassword']);

Route::middleware('auth:api')->group(function () {
    Route::get('me', [AuthController::class, 'me']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::get('roles', [AuthController::class, 'getRoleList']);

    Route::get('departments', [DepartmentController::class, 'index']);
    Route::post('department/store', [DepartmentController::class, 'store']);
    Route::post('department/delete', [DepartmentController::class, 'delete']);
    Route::get('department/detail/{id}', [DepartmentController::class, 'detail']);
    Route::post('department/update', [DepartmentController::class, 'update']);

    Route::get('users', [UserController::class, 'index']);
    Route::post('user/store', [UserController::class, 'store']);
    Route::get('users/{id}', [UserController::class, 'show']);
    Route::get('users/{id}/activity-logs', [UserController::class, 'activityLogs']);
    Route::post('user/delete', [UserController::class, 'delete']);
    Route::post('user/update', [UserController::class, 'update']);

    Route::get('categories', [CategoryController::class, 'index']);
    Route::post('category/store', [CategoryController::class, 'store']);
    Route::post('category/delete', [CategoryController::class, 'delete']);
    Route::get('category/{id}', [CategoryController::class, 'show']);
    Route::post('category/update', [CategoryController::class, 'update']);

    // account setting
    Route::get('account-setting', [ConfigController::class, 'index']);
    Route::post('account-setting', [ConfigController::class, 'update']);

    Route::post('change-password', [AuthController::class, 'changePassword']);
});
