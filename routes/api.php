<?php

declare(strict_types = 1);

use Canalizador\Script\Infrastructure\Http\Api\Controllers\GenerateScriptController;
use Illuminate\Support\Facades\Route;

Route::post('/scripts/generate', GenerateScriptController::class);
