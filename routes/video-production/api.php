<?php

declare(strict_types = 1);

use App\Http\Middleware\EnsureGoogleToken;
use Canalizador\VideoProduction\Avatar\Infrastructure\Http\Api\Controllers\CreateAvatarController;
use Canalizador\VideoProduction\Avatar\Infrastructure\Http\Api\Controllers\UpdateAvatarController;
use Canalizador\VideoProduction\News\Infrastructure\Http\Api\Controllers\DownloadNewsController;
use Canalizador\VideoProduction\Video\Infrastructure\Http\Api\Controllers\ApplyVoiceController;
use Canalizador\VideoProduction\Video\Infrastructure\Http\Api\Controllers\CreateVideoController;
use Canalizador\VideoProduction\Video\Infrastructure\Http\Api\Controllers\RetrieveVideoContentController;
use Canalizador\VideoProduction\Voice\Infrastructure\Http\Api\Controllers\CloneVoiceController;
use Canalizador\VideoProduction\Voice\Infrastructure\Http\Api\Controllers\GenerateVoiceController;
use Canalizador\VideoProduction\Weather\Infrastructure\Http\Api\Controllers\GetForecastsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api.token'])->group(function () {
    Route::post('/avatars', CreateAvatarController::class);
    Route::put('/avatars/{avatarId}', UpdateAvatarController::class);

    Route::post('/videos/create', CreateVideoController::class);
    Route::get('/videos/{videoId}/content', RetrieveVideoContentController::class)
        ->middleware(EnsureGoogleToken::class);
    Route::post('/videos/{videoId}/apply-voice', ApplyVoiceController::class);

    Route::post('/voice/clone', CloneVoiceController::class);
    Route::post('/voice/generate', GenerateVoiceController::class);

    Route::post('/news/download', DownloadNewsController::class);
    Route::get('/weather', GetForecastsController::class);
});
