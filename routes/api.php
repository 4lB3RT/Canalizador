<?php

declare(strict_types = 1);

use App\Http\Middleware\EnsureGoogleToken;
use Canalizador\Avatar\Infrastructure\Http\Api\Controllers\CreateAvatarController;
use Canalizador\Channel\Infrastructure\Http\Api\Controllers\SyncChannelController;
use Canalizador\Channel\Infrastructure\Http\Api\Controllers\UpdateChannelWithAIController;
use Canalizador\News\Infrastructure\Http\Api\Controllers\DownloadNewsController;
use Canalizador\Video\Infrastructure\Http\Api\Controllers\ApplyVoiceController;
use Canalizador\Video\Infrastructure\Http\Api\Controllers\CreateVideoController;
use Canalizador\Voice\Infrastructure\Http\Api\Controllers\CloneVoiceController;
use Canalizador\Voice\Infrastructure\Http\Api\Controllers\GenerateVoiceController;
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
    Route::post('/news/download', DownloadNewsController::class);
    Route::post('/videos/{videoId}/apply-voice', ApplyVoiceController::class);
    Route::post('/voice/clone', CloneVoiceController::class);
    Route::post('/voice/generate', GenerateVoiceController::class);
});
