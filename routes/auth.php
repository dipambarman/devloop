<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\GithubAuthController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    // Pure GitHub OAuth Entrypoints
    Route::get('login', [GithubAuthController::class, 'showLogin'])
        ->name('login');

    Route::get('register', [GithubAuthController::class, 'showRegister'])
        ->name('register');

    Route::get('auth/github', [GithubAuthController::class, 'redirect'])
        ->name('auth.github');

    Route::get('auth/github/callback', [GithubAuthController::class, 'callback'])
        ->name('auth.github.callback');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
