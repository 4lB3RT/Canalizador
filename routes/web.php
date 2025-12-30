<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::get('/register', [RegisterController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

Route::get('/auth/google/login', [LoginController::class, 'handleGoogleLogin'])->name('auth.google.login');
Route::get('/auth/google/register', [RegisterController::class, 'handleGoogleRegister'])->name('auth.google.register');

Route::get('/auth/google/callback', function (Request $request) {
    $oauthType = session('oauth_type', 'register');

    if ($oauthType === 'login') {
        $controller = app(LoginController::class);
        return $controller->handleGoogleCallback($request);
    } else {
        $controller = app(RegisterController::class);
        return $controller->handleGoogleCallback($request);
    }
})->name('auth.google.callback');

Route::middleware(['auth'])->group(function () {
    Route::get('/', function () {
        return view('welcome');
    });
});

