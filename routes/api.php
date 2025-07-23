<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;

/*
|--------------------------------------------------------------------------
| API Routes - v1/auth
|--------------------------------------------------------------------------
*/
Route::prefix('v1/auth')->group(function () {
    // Public routes
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);
    Route::post('verify-email/{id}/{hash}', [AuthController::class, 'verifyEmail'])->name('verification.verify');
    Route::post('resend-email', [AuthController::class, 'resendEmailVerification']);
    Route::post('send-sms-code', [AuthController::class, 'sendSmsCode']);
    Route::post('verify-sms', [AuthController::class, 'verifySmsCode']);

    // Protected routes (must be logged in with JWT)
    Route::middleware('jwt')->group(function () {
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::post('update-profile', [AuthController::class, 'updateProfile']);
        Route::post('change-password', [AuthController::class, 'changePassword']); // ✅ Any logged-in user can change password
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
    });
});

/*
|--------------------------------------------------------------------------
| API Routes - v1/admin
|--------------------------------------------------------------------------
*/
Route::prefix('v1/admin')->middleware('jwt')->group(function () {
    // Routes accessible by 'admin' or 'super-admin'
    Route::middleware('role:admin,super-admin')->group(function () {
        Route::get('dashboard', [AdminController::class, 'dashboard']);
        Route::get('users', [AdminController::class, 'userList']);
    });

    // Routes accessible by 'super-admin' only
    Route::middleware('role:super-admin')->group(function () {
        Route::get('super-admin-only', [AdminController::class, 'superAdminOnly']);
    });
});
