<?php

declare(strict_types = 1);

use Canalizador\Script\Infrastructure\Http\Api\Controllers\GenerateScriptController;
use Canalizador\Video\Infrastructure\Http\Api\Controllers\GenerateVideoController;
use Canalizador\Video\Infrastructure\Http\Api\Controllers\RetrieveVideoContentController;
use Illuminate\Support\Facades\Route;

Route::post('/scripts/generate', GenerateScriptController::class);
Route::post('/videos/generate', GenerateVideoController::class);
Route::get('/videos/{videoId}/content', RetrieveVideoContentController::class);
