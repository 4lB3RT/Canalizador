<?php

declare(strict_types=1);

use App\Http\Controllers\GoogleChannelOAuthController;
use Canalizador\Shared\Header\Infrastructure\Http\Api\Controllers\GetHeaderController;
use Canalizador\Shared\Profile\Infrastructure\Http\Api\Controllers\UpdateProfileController;
use Canalizador\YouTube\Channel\Infrastructure\Http\Api\Controllers\GetPendingOAuthStateController;
use Canalizador\YouTube\Channel\Infrastructure\Http\Api\Controllers\LinkChannelController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api.token'])->group(function () {
    Route::get('/header', GetHeaderController::class);
    Route::post('/profile', UpdateProfileController::class);

    Route::post('/youtube/channels/oauth/start', [GoogleChannelOAuthController::class, 'startApi']);
    Route::get('/youtube/channels/oauth/{state}', GetPendingOAuthStateController::class);
    Route::post('/youtube/channels/link', LinkChannelController::class);
});
