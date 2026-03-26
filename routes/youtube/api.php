<?php

declare(strict_types = 1);

use App\Http\Middleware\EnsureGoogleToken;
use Canalizador\YouTube\Channel\Infrastructure\Http\Api\Controllers\SyncChannelController;
use Canalizador\YouTube\Channel\Infrastructure\Http\Api\Controllers\UpdateChannelWithAIController;
use Canalizador\YouTube\Video\Infrastructure\Http\Api\Controllers\DownloadLatestChannelVideoController;
use Canalizador\YouTube\Video\Infrastructure\Http\Api\Controllers\FragmentAndPublishVideoController;
use Canalizador\YouTube\Video\Infrastructure\Http\Api\Controllers\PublishVideoController;
use Canalizador\YouTube\Video\Infrastructure\Http\Api\Controllers\SmartFragmentAndPublishVideoController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api.token'])->group(function () {
    Route::put('/channels/{channelId}/sync', SyncChannelController::class)
        ->middleware(EnsureGoogleToken::class);
    Route::put('/channels/{channelId}/update-with-ai', UpdateChannelWithAIController::class)
        ->middleware(EnsureGoogleToken::class);
    Route::post('/channels/{channelId}/download-latest', DownloadLatestChannelVideoController::class)
        ->middleware(EnsureGoogleToken::class);
    Route::post('/videos/publish', PublishVideoController::class)
        ->middleware(EnsureGoogleToken::class);
    Route::post('/videos/fragment-and-publish', FragmentAndPublishVideoController::class)
        ->middleware(EnsureGoogleToken::class);
    Route::post('/videos/smart-fragment-and-publish', SmartFragmentAndPublishVideoController::class)
        ->middleware(EnsureGoogleToken::class);
});
