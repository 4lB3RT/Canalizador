<?php

declare(strict_types = 1);

use App\Http\Middleware\EnsureGoogleToken;
use Helmreel\VideoProduction\Avatar\Infrastructure\Http\Api\Controllers\CreateAvatarController;
use Helmreel\VideoProduction\Avatar\Infrastructure\Http\Api\Controllers\DeleteAvatarController;
use Helmreel\VideoProduction\Avatar\Infrastructure\Http\Api\Controllers\GenerateAvatarMetadataController;
use Helmreel\VideoProduction\Avatar\Infrastructure\Http\Api\Controllers\GetAvatarController;
use Helmreel\VideoProduction\Avatar\Infrastructure\Http\Api\Controllers\GetAvatarsController;
use Helmreel\VideoProduction\Avatar\Infrastructure\Http\Api\Controllers\UpdateAvatarController;
use Helmreel\VideoProduction\Image\Infrastructure\Http\Api\Controllers\GetImageFileController;
use Helmreel\VideoProduction\Media\Infrastructure\Http\Api\Controllers\GetMediaFileController;
use Helmreel\VideoProduction\News\Infrastructure\Http\Api\Controllers\DownloadNewsController;
use Helmreel\VideoProduction\Script\Infrastructure\Http\Api\Controllers\CreateScriptController;
use Helmreel\VideoProduction\Script\Infrastructure\Http\Api\Controllers\DeleteScriptController;
use Helmreel\VideoProduction\Script\Infrastructure\Http\Api\Controllers\GetScriptController;
use Helmreel\VideoProduction\Script\Infrastructure\Http\Api\Controllers\GetScriptsController;
use Helmreel\VideoProduction\Script\Infrastructure\Http\Api\Controllers\UpdateScriptController;
use Helmreel\VideoProduction\Video\Infrastructure\Http\Api\Controllers\ApplyVoiceController;
use Helmreel\VideoProduction\Video\Infrastructure\Http\Api\Controllers\CreateVideoController;
use Helmreel\VideoProduction\Video\Infrastructure\Http\Api\Controllers\GetVideoController;
use Helmreel\VideoProduction\Video\Infrastructure\Http\Api\Controllers\GetVideoFileController;
use Helmreel\VideoProduction\Video\Infrastructure\Http\Api\Controllers\GetVideosController;
use Helmreel\VideoProduction\Video\Infrastructure\Http\Api\Controllers\RetrieveVideoContentController;
use Helmreel\VideoProduction\Voice\Infrastructure\Http\Api\Controllers\CloneVoiceController;
use Helmreel\VideoProduction\Voice\Infrastructure\Http\Api\Controllers\GenerateVoiceController;
use Helmreel\VideoProduction\Voice\Infrastructure\Http\Api\Controllers\DeleteVoiceController;
use Helmreel\VideoProduction\Voice\Infrastructure\Http\Api\Controllers\GetVoicesController;
use Helmreel\VideoProduction\Voice\Infrastructure\Http\Api\Controllers\UpdateVoiceController;
use Helmreel\VideoProduction\Weather\Infrastructure\Http\Api\Controllers\GetForecastsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api.token'])->group(function () {
    Route::get('/avatars', GetAvatarsController::class);
    Route::get('/avatars/{avatarId}', GetAvatarController::class);
    Route::post('/avatars', CreateAvatarController::class);
    Route::post('/avatars/{avatarId}/metadata', GenerateAvatarMetadataController::class);
    Route::put('/avatars/{avatarId}', UpdateAvatarController::class);
    Route::delete('/avatars/{avatarId}', DeleteAvatarController::class);

    Route::get('/images/{imageId}', GetImageFileController::class);
    Route::get('/media/{mediaId}', GetMediaFileController::class);

    Route::get('/scripts', GetScriptsController::class);
    Route::get('/scripts/{scriptId}', GetScriptController::class);
    Route::post('/scripts', CreateScriptController::class);
    Route::put('/scripts/{scriptId}', UpdateScriptController::class);
    Route::delete('/scripts/{scriptId}', DeleteScriptController::class);

    Route::get('/videos', GetVideosController::class);
    Route::post('/videos/create', CreateVideoController::class);
    Route::get('/videos/{videoId}/content', RetrieveVideoContentController::class)
        ->middleware(EnsureGoogleToken::class);
    Route::post('/videos/{videoId}/apply-voice', ApplyVoiceController::class);
    Route::get('/videos/{videoId}', GetVideoController::class);

    Route::get('/voices', GetVoicesController::class);
    Route::post('/voice/clone', CloneVoiceController::class);
    Route::post('/voices/{voiceId}/generate', GenerateVoiceController::class);
    Route::put('/voices/{voiceId}', UpdateVoiceController::class);
    Route::delete('/voices/{voiceId}', DeleteVoiceController::class);

    Route::post('/news/download', DownloadNewsController::class);
    Route::get('/weather', GetForecastsController::class);
});
