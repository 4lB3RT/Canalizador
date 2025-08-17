<?php

use Canalizador\Recommendation\Infrastructure\Controllers\GetRecommendationsByVideoIdController;
use Canalizador\Video\Infrastructure\Http\Api\Controllers\GetYoutubeVideoController;
use Canalizador\Video\Infrastructure\Http\Api\Controllers\GetVideoTranscriptionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['google.token'])->group(function () {
    Route::get('/youtube-video/{id}', [GetYoutubeVideoController::class, '__invoke'])->name('youtube.video');

    Route::get('/recommendations/videos/{videoId}', GetRecommendationsByVideoIdController::class)
        ->name('recommendations.by_video');

    Route::get('/transcriptor/videos/{videoId}', GetVideoTranscriptionController::class)
        ->name('transcriptor.by_video');

    Route::get('/auth/google/callback')->name('google.callback');
});
