<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

final class RegisterController
{
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    public function register(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $apiToken = $user->generateApiToken();

        Auth::login($user);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'api_token' => $apiToken,
            ], 201);
        }

        return redirect()->intended('/')->with('success', '¡Cuenta creada exitosamente!');
    }

    public function handleGoogleRegister(): RedirectResponse
    {
        $client = $this->buildGoogleClient();

        session(['oauth_type' => 'register']);

        $authUrl = $client->createAuthUrl();
        return redirect($authUrl);
    }

    private function buildGoogleClient(): \Google_Client
    {
        $client = new \Google_Client();
        $client->setClientId(config('services.youtube_analytics.client_id'));
        $client->setClientSecret(config('services.youtube_analytics.client_secret'));
        $client->setRedirectUri(config('services.youtube_analytics.redirect_uri'));

        $client->setScopes([
            'https://www.googleapis.com/auth/userinfo.email',
            'https://www.googleapis.com/auth/userinfo.profile',
        ]);

        $client->setAccessType('online');

        return $client;
    }
}
