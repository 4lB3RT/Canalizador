<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

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

    /**
     * @throws \Exception
     */
    public function handleGoogleCallback(Request $request): JsonResponse|RedirectResponse
    {
        if (!$request->has('code')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Código de autorización no proporcionado',
                ], 400);
            }

            return redirect()->route('register')->with('error', 'Error en la autenticación con Google');
        }

        try {
            $client = $this->buildGoogleClient();
            $token = $client->fetchAccessTokenWithAuthCode($request->get('code'));

            if (isset($token['error'])) {
                Log::error('Google OAuth error', ['error' => $token]);

                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => 'Error en la autenticación con Google',
                        'message' => $token['error_description'] ?? 'Error desconocido',
                    ], 400);
                }

                return redirect()->route('register')->with('error', 'Error en la autenticación con Google');
            }

            $client->setAccessToken($token);

            $oauth2 = new \Google_Service_Oauth2($client);
            $googleUser = $oauth2->userinfo->get();

            $email = $googleUser->email;

            if (!$email) {
                $errorMessage = 'No se pudo obtener un email válido de tu cuenta de Google. Por favor, asegúrate de tener un email verificado en tu cuenta.';

                Log::warning('Invalid Google email', [
                    'google_user' => [
                        'id' => $googleUser->id ?? null,
                        'email' => $googleUser->email ?? null,
                        'verified_email' => $googleUser->verified_email ?? null,
                    ],
                ]);

                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => 'Email inválido',
                        'message' => $errorMessage,
                    ], 400);
                }

                return redirect()->route('register')->with('error', $errorMessage);
            }

            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $googleUser->name ?? 'Usuario de Google',
                    'email' => $email,
                    'password' => Hash::make(\Illuminate\Support\Str::random(32)),
                    'google_access_token' => $token['access_token'] ?? null,
                    'google_refresh_token' => $token['refresh_token'] ?? null,
                    'google_expires_in' => $token['expires_in'] ?? null,
                    'google_scope' => $token['scope'] ?? null,
                    'google_token_type' => $token['token_type'] ?? null,
                ]

            );

            if ($user->wasRecentlyCreated === false) {
                $user->update([
                    'google_access_token' => $token['access_token'] ?? $user->google_access_token,
                    'google_refresh_token' => $token['refresh_token'] ?? $user->google_refresh_token,
                    'google_expires_in' => $token['expires_in'] ?? $user->google_expires_in,
                    'google_scope' => $token['scope'] ?? $user->google_scope,
                    'google_token_type' => $token['token_type'] ?? $user->google_token_type,
                ]);
            }

            if (!$user->api_token) {
                $apiToken = $user->generateApiToken();
            } else {
                $apiToken = $user->generateApiToken();
            }

            Auth::login($user);


            session()->forget('oauth_type');

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

        } catch (\Exception $e) {
            Log::error('Error processing Google OAuth callback', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Error procesando la autenticación',
                    'message' => $e->getMessage(),
                ], 500);
            }

            return redirect()->route('register')->with('error', 'Error procesando la autenticación: ' . $e->getMessage());
        }
    }


    private function buildGoogleClient(): \Google_Client
    {
        $client = new \Google_Client();
        $client->setClientId(config('services.youtube_analytics.client_id'));
        $client->setClientSecret(config('services.youtube_analytics.client_secret'));
        $client->setRedirectUri(config('services.youtube_analytics.redirect_uri'));

        $client->setScopes([
            'https://www.googleapis.com/auth/yt-analytics.readonly',
            'https://www.googleapis.com/auth/youtube.upload',
            'https://www.googleapis.com/auth/youtube',
            'https://www.googleapis.com/auth/userinfo.email',
            'https://www.googleapis.com/auth/userinfo.profile',
        ]);

        $client->setAccessType('offline');
        $client->setPrompt('consent');

        return $client;
    }
}
