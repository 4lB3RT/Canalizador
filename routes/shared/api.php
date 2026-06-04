<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\GoogleChannelOAuthController;
use Helmreel\Shared\Header\Infrastructure\Http\Api\Controllers\GetHeaderController;
use Helmreel\Shared\HealthCheck\Infrastructure\Http\Api\Controllers\GetHealthController;
use Helmreel\Shared\Profile\Infrastructure\Http\Api\Controllers\UpdateProfileController;
use Helmreel\YouTube\Channel\Infrastructure\Http\Api\Controllers\GetPendingOAuthStateController;
use Helmreel\YouTube\Channel\Infrastructure\Http\Api\Controllers\LinkChannelController;
use Illuminate\Support\Facades\Route;

Route::get('/health-check', GetHealthController::class);

Route::post('/login', [LoginController::class, 'login']);
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink']);
Route::post('/reset-password', [ResetPasswordController::class, 'reset']);

Route::middleware(['api.token'])->group(function () {
    Route::get('/header', GetHeaderController::class);
    Route::post('/profile', UpdateProfileController::class);

    Route::post('/youtube/channels/oauth/start', [GoogleChannelOAuthController::class, 'startApi']);
    Route::get('/youtube/channels/oauth/{state}', GetPendingOAuthStateController::class);
    Route::post('/youtube/channels/link', LinkChannelController::class);
});
