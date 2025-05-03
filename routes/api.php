<?php

use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ReplyController;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\UnLikeController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\IdeaController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LikeController;
use Illuminate\Support\Facades\Route;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('reset-password', [AuthController::class, 'forgotPassword']);
Route::get('export/idea-list', [IdeaController::class, 'export']);
Route::post('password-reset/request', [AuthController::class, 'requestPasswordReset']);

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
    Route::post('user/disable', [UserController::class, 'disable']);
    Route::post('user/enable', [UserController::class, 'enable']);
    Route::post('user/update', [UserController::class, 'update']);
    Route::post('user/profile-image', [UserController::class, 'updateProfileImage']);

    Route::get('categories', [CategoryController::class, 'index']);
    Route::post('category/store', [CategoryController::class, 'store']);
    Route::post('category/delete', [CategoryController::class, 'delete']);
    Route::get('category/{id}', [CategoryController::class, 'show']);
    Route::post('category/update', [CategoryController::class, 'update']);

    Route::get('ideas', [IdeaController::class, 'index']);
    Route::post('idea/store', [IdeaController::class, 'store']);
    Route::get('idea/{id}', [IdeaController::class, 'show']);
    Route::post('idea/update', [IdeaController::class, 'update']);
    Route::post('idea/delete', [IdeaController::class, 'delete']);
    Route::post('idea/report', [IdeaController::class, 'report']);
    Route::post('idea/hide', [IdeaController::class, 'hide']);
    Route::post('idea/unhide', [IdeaController::class, 'unhide']);
   

    Route::get('academic-years', [AcademicYearController::class, 'index']);
    Route::post('academic-year/store', [AcademicYearController::class, 'store']);
    Route::post('academic-year/delete', [AcademicYearController::class, 'delete']);
    Route::get('academic-year/detail/{id}', [AcademicYearController::class, 'detail']);
    Route::post('academic-year/update', [AcademicYearController::class, 'update']);

    //Comments
    Route::post('comment/store', [CommentController::class, 'store']);
    Route::post('reply/store', [ReplyController::class, 'store']);

     //Like
     Route::post('like/store', [LikeController::class, 'like']);
     Route::post('like/remove', [LikeController::class, 'removeLike']);

     //unlike
     Route::post('unlike/store', [UnLikeController::class, 'unlike']);
     Route::post('unlike/remove', [UnLikeController::class, 'removeUnLike']);

    // account setting
    Route::get('account-setting', [ConfigController::class, 'index']);
    Route::post('account-setting', [ConfigController::class, 'update']);

    Route::post('change-password', [AuthController::class, 'changePassword']);
    Route::get('insight', [ConfigController::class, 'insight']);
   
});
Route::post('store-view', [AuthController::class, 'storeView']);
