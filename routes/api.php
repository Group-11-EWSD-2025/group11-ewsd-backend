<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\UserController;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::get('me', [AuthController::class, 'me']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);

    Route::get('departments', [DepartmentController::class, 'index']);
    Route::post('department/store', [DepartmentController::class, 'store']);
    Route::post('department/delete', [DepartmentController::class, 'delete']);

    Route::get('users', [UserController::class, 'index']);
    Route::post('user/store', [UserController::class, 'store']);
});
