<?php

declare(strict_types = 1);

use App\Http\Middleware\EnsureGoogleToken;
use Helmreel\YouTube\Channel\Infrastructure\Http\Api\Controllers\GetChannelController;
use Helmreel\YouTube\Channel\Infrastructure\Http\Api\Controllers\GetChannelsController;
use Helmreel\YouTube\Channel\Infrastructure\Http\Api\Controllers\RegisterChannelController;
use Helmreel\YouTube\Channel\Infrastructure\Http\Api\Controllers\SyncChannelController;
use Helmreel\YouTube\Channel\Infrastructure\Http\Api\Controllers\UpdateChannelController;
use Helmreel\YouTube\Channel\Infrastructure\Http\Api\Controllers\UpdateChannelWithAIController;
use Helmreel\YouTube\Video\Infrastructure\Http\Api\Controllers\DownloadLatestChannelVideoController;
use Helmreel\YouTube\Video\Infrastructure\Http\Api\Controllers\FragmentAndPublishVideoController;
use Helmreel\YouTube\Video\Infrastructure\Http\Api\Controllers\GenerateShortController;
use Helmreel\YouTube\Video\Infrastructure\Http\Api\Controllers\GetChannelVideosController;
use Helmreel\YouTube\Video\Infrastructure\Http\Api\Controllers\GetVideosController;
use Helmreel\YouTube\Video\Infrastructure\Http\Api\Controllers\PublishVideoController;
use Helmreel\YouTube\Video\Infrastructure\Http\Api\Controllers\SmartFragmentAndPublishVideoController;
use Helmreel\YouTube\Video\Infrastructure\Http\Api\Controllers\SyncVideoController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api.token'])->group(function () {
    Route::get('/channels', GetChannelsController::class);
    Route::get('/channels/{channelId}', GetChannelController::class);
    Route::put('/channels/{channelId}', UpdateChannelController::class);
    Route::get('/channels/{channelId}/videos', GetChannelVideosController::class);
    Route::get('/videos', GetVideosController::class);
    Route::post('/channels/{channelId}/register', RegisterChannelController::class)
        ->middleware(EnsureGoogleToken::class);
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
    Route::post('/videos/{video_id}/shorts/generate', GenerateShortController::class)
        ->middleware(EnsureGoogleToken::class);
    Route::post('/videos/sync', SyncVideoController::class)
        ->middleware(EnsureGoogleToken::class);
});
