<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class LoginController
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Credenciales inválidas',
                ], 401);
            }

            return back()->withErrors([
                'email' => 'Las credenciales proporcionadas no son correctas.',
            ])->withInput($request->only('email'));
        }

        $user = Auth::user();

        if (!$user->api_token) {
            $apiToken = $user->generateApiToken();
        } else {
            $apiToken = $user->generateApiToken();
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'api_token' => $apiToken,
            ]);
        }

        return redirect()->intended('/')->with('success', '¡Bienvenido de nuevo!');
    }

    public function handleGoogleLogin(): RedirectResponse
    {
        $client = $this->buildGoogleClient();

        session(['oauth_type' => 'login']);

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

            return redirect()->route('login')->with('error', 'Error en la autenticación con Google');
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

                return redirect()->route('login')->with('error', 'Error en la autenticación con Google');
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

                return redirect()->route('login')->with('error', $errorMessage);
            }

            $user = User::where('email', $email)->first();

            if (!$user) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => 'Usuario no encontrado',
                        'message' => 'Por favor regístrate primero',
                    ], 404);
                }

                return redirect()->route('register')->with('error', 'Usuario no encontrado. Por favor regístrate primero.');
            }

            $user->update([
                'google_access_token' => $token['access_token'] ?? null,
                'google_refresh_token' => $token['refresh_token'] ?? $user->google_refresh_token,
                'google_expires_in' => $token['expires_in'] ?? null,
                'google_scope' => $token['scope'] ?? null,
                'google_token_type' => $token['token_type'] ?? null,
            ]);

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
                ]);
            }

            return redirect()->intended('/')->with('success', '¡Bienvenido de nuevo!');

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

            return redirect()->route('login')->with('error', 'Error procesando la autenticación: ' . $e->getMessage());
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
