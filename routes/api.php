<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;

Route::prefix('v1/auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::middleware('api')->group(function () {
        Route::post('login', [AuthController::class, 'login']);
        Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('reset-password', [AuthController::class, 'resetPassword']);
        Route::post('verify-email/{id}/{hash}', [AuthController::class, 'verifyEmail'])->name('verification.verify');
        Route::post('resend-email', [AuthController::class, 'resendEmailVerification']);
        Route::post('send-sms-code', [AuthController::class, 'sendSmsCode']);
        Route::post('verify-sms', [AuthController::class, 'verifySmsCode']);
        Route::post('refresh', [AuthController::class, 'refresh']);
    });


    Route::prefix('oauth')->group(function () {
        Route::get('{provider}/redirect', [AuthController::class, 'redirectToProvider']);
        Route::get('{provider}/callback', [AuthController::class, 'handleProviderCallback']);
    });

    Route::middleware(['auth:api', 'verified'])->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
    });
});

Route::prefix('v1/admin')->group(function () {
    Route::get('dashboard', [AdminController::class, 'dashboard']);
    Route::get('users', [AdminController::class, 'userList']);
    Route::get('super-admin-only', [AdminController::class, 'superAdminOnly']);
});
