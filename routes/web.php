<?php

use Canalizador\Video\Infrastructure\Http\Api\Controllers\GetYoutubeVideoController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['google.token'])->group(function () {
    Route::get('/youtube-video/{id}', [GetYoutubeVideoController::class, '__invoke'])->name('youtube.video');

    Route::get('/auth/google/callback')->name('google.callback');
});

