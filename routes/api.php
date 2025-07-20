<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;

Route::prefix('v1/auth')->group(function () {
    // Public routes (no auth)
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']); // Changed order
    Route::get('verify-email/{id}/{hash}', [AuthController::class, 'verifyEmail'])->name('verification.verify'); // Changed to GET
    Route::post('resend-email', [AuthController::class, 'resendEmailVerification']);
    Route::post('send-sms-code', [AuthController::class, 'sendSmsCode']);
    Route::post('verify-sms', [AuthController::class, 'verifySmsCode']);
    Route::post('refresh', [AuthController::class, 'refresh']);

    // OAuth routes
    Route::prefix('oauth')->group(function () {
        Route::get('{provider}/redirect', [AuthController::class, 'redirectToProvider']);
        Route::get('{provider}/callback', [AuthController::class, 'handleProviderCallback']);
    });

    // 🔒 AUTH-PROTECTED ROUTES (UNCOMMENTED)
    Route::middleware(['auth:api', 'verified'])->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
        Route::post('change-password', [AuthController::class, 'changePassword']); // Moved inside auth
    });
});

// 🔒 ADMIN ROUTES WITH MIDDLEWARE
Route::prefix('v1/admin')->middleware(['auth:api', 'verified', 'admin'])->group(function () {
    Route::get('dashboard', [AdminController::class, 'dashboard']);
    Route::get('users', [AdminController::class, 'userList']);
    Route::get('super-admin-only', [AdminController::class, 'superAdminOnly'])->middleware('role:super-admin');
});
