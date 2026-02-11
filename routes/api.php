<?php

declare(strict_types = 1);

use App\Http\Middleware\EnsureGoogleToken;
use Canalizador\Avatar\Infrastructure\Http\Api\Controllers\CreateAvatarController;
use Canalizador\Channel\Infrastructure\Http\Api\Controllers\SyncChannelController;
use Canalizador\Channel\Infrastructure\Http\Api\Controllers\UpdateChannelWithAIController;
use Canalizador\Video\Infrastructure\Http\Api\Controllers\CreateVideoController;
use Canalizador\Video\Infrastructure\Http\Api\Controllers\PublishVideoController;
use Canalizador\Video\Infrastructure\Http\Api\Controllers\RetrieveVideoContentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api.token'])->group(function () {
    Route::post('/avatars', CreateAvatarController::class);
    Route::post('/videos/create', CreateVideoController::class);
    Route::get('/videos/{videoId}/content', RetrieveVideoContentController::class)
        ->middleware(EnsureGoogleToken::class);
    Route::post('/videos/publish', PublishVideoController::class)
        ->middleware(EnsureGoogleToken::class);
    Route::put('/channels/{channelId}/update-with-ai', UpdateChannelWithAIController::class)
        ->middleware(EnsureGoogleToken::class);
    Route::put('/channels/{channelId}/sync', SyncChannelController::class)
        ->middleware(EnsureGoogleToken::class);
});
