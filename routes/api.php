<?php

declare(strict_types = 1);

use App\Http\Middleware\EnsureGoogleToken;
use Canalizador\VideoProduction\Avatar\Infrastructure\Http\Api\Controllers\CreateAvatarController;
use Canalizador\VideoProduction\Avatar\Infrastructure\Http\Api\Controllers\UpdateAvatarController;
use Canalizador\YouTube\Channel\Infrastructure\Http\Api\Controllers\SyncChannelController;
use Canalizador\YouTube\Channel\Infrastructure\Http\Api\Controllers\UpdateChannelWithAIController;
use Canalizador\VideoProduction\News\Infrastructure\Http\Api\Controllers\DownloadNewsController;
use Canalizador\VideoProduction\Video\Infrastructure\Http\Api\Controllers\ApplyVoiceController;
use Canalizador\VideoProduction\Video\Infrastructure\Http\Api\Controllers\CreateVideoController;
use Canalizador\VideoProduction\Voice\Infrastructure\Http\Api\Controllers\CloneVoiceController;
use Canalizador\VideoProduction\Voice\Infrastructure\Http\Api\Controllers\GenerateVoiceController;
use Canalizador\VideoProduction\Video\Infrastructure\Http\Api\Controllers\RetrieveVideoContentController;
use Canalizador\VideoProduction\Weather\Infrastructure\Http\Api\Controllers\GetForecastsController;
use Canalizador\YouTube\Video\Infrastructure\Http\Api\Controllers\DownloadLatestChannelVideoController;
use Canalizador\YouTube\Video\Infrastructure\Http\Api\Controllers\FragmentAndPublishVideoController;
use Canalizador\YouTube\Video\Infrastructure\Http\Api\Controllers\PublishVideoController;
use Canalizador\YouTube\Video\Infrastructure\Http\Api\Controllers\SmartFragmentAndPublishVideoController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api.token'])->group(function () {
    Route::post('/avatars', CreateAvatarController::class);
    Route::put('/avatars/{avatarId}', UpdateAvatarController::class);
    Route::post('/videos/create', CreateVideoController::class);
    Route::get('/videos/{videoId}/content', RetrieveVideoContentController::class)
        ->middleware(EnsureGoogleToken::class);
    Route::post('/videos/{videoId}/apply-voice', ApplyVoiceController::class);
    Route::put('/channels/{channelId}/update-with-ai', UpdateChannelWithAIController::class)
        ->middleware(EnsureGoogleToken::class);
    Route::put('/channels/{channelId}/sync', SyncChannelController::class)
        ->middleware(EnsureGoogleToken::class);
    Route::post('/news/download', DownloadNewsController::class);
    Route::post('/voice/clone', CloneVoiceController::class);
    Route::post('/voice/generate', GenerateVoiceController::class);
    Route::get('/weather', GetForecastsController::class);

    // BC YouTube
    Route::post('/youtube/videos/publish', PublishVideoController::class)
        ->middleware(EnsureGoogleToken::class);
    Route::post('/youtube/channels/{channelId}/download-latest', DownloadLatestChannelVideoController::class)
        ->middleware(EnsureGoogleToken::class);
    Route::post('/youtube/videos/fragment-and-publish', FragmentAndPublishVideoController::class)
        ->middleware(EnsureGoogleToken::class);
    Route::post('/youtube/videos/smart-fragment-and-publish', SmartFragmentAndPublishVideoController::class)
        ->middleware(EnsureGoogleToken::class);
});
