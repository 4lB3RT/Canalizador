<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\GoogleChannelOAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::get('/register', [RegisterController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink'])->name('password.email');
Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');

Route::get('/auth/google/login', [LoginController::class, 'handleGoogleLogin'])->name('auth.google.login');
Route::get('/auth/google/register', [RegisterController::class, 'handleGoogleRegister'])->name('auth.google.register');

Route::get('/auth/google/channel/callback', [GoogleChannelOAuthController::class, 'callback'])
    ->name('auth.google.channel.callback');

// Único callback para todos los flujos de Google (botón clásico y One Tap/silencioso).
// No depende de session('oauth_type'): decide login vs registro por email dentro del método.
Route::get('/auth/google/callback', [LoginController::class, 'handleGoogleCallback'])
    ->name('auth.google.callback');

Route::middleware(['auth'])->group(function () {
    Route::get('/', function () {
        return view('welcome');
    });
});

