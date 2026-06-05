<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Services\GoogleTokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
     * @throws \Throwable
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

            // Flujo unificado: si el usuario existe inicia sesión; si no, se crea (registro
            // implícito). Esto soporta tanto el botón clásico como Google One Tap/silencioso,
            // que llega sin session('oauth_type').
            // TODO(human): resolver $user a partir de $email y $googleUser, persistiendo los
            // tokens de Google. Implementa aquí la resolución y asígnala a $user.
            $user = $this->resolveUserFromGoogle($email, $googleUser, $token);

            app(GoogleTokenService::class)->storeToken($user->id, $token);

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

            // SPA flow: Google returns here via a browser navigation (not JSON),
            // so hand the token to the front-end. We pass it in the URL fragment
            // (#…) rather than the query string so it never reaches the server,
            // logs or the Referer header. The SPA reads it on boot and stores it.
            $frontend = config('services.youtube_analytics.frontend_redirect_uri');
            if ($frontend) {
                $params = http_build_query([
                    'token' => $apiToken,
                    'name' => $user->name,
                    'email' => $user->email,
                    'id' => $user->id,
                ]);

                return redirect()->away(rtrim($frontend, '/') . '/auth/callback#' . $params);
            }

            return redirect()->intended('/')->with('success', '¡Bienvenido de nuevo!');

        } catch (\Throwable $e) {
            Log::error('Error processing Google OAuth callback', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Error procesando la autenticación',
                    'message' => 'No se pudo completar la autenticación. Inténtalo de nuevo.',
                ], 500);
            }

            $frontend = config('services.youtube_analytics.frontend_redirect_uri');
            if ($frontend) {
                return redirect()->away(rtrim($frontend, '/') . '/login#error=oauth_failed');
            }

            return redirect()->route('login')->with('error', 'No se pudo completar la autenticación. Inténtalo de nuevo.');
        }
    }

    /**
     * Resuelve (o crea) el usuario a partir de los datos de Google y persiste sus tokens.
     *
     * @param  array<string, mixed>  $token
     */
    private function resolveUserFromGoogle(string $email, \Google_Service_Oauth2_Userinfo $googleUser, array $token): User
    {
        $user = User::firstOrNew(['email' => $email]);

        if (!$user->exists) {
            $user->name = $googleUser->name ?? 'Usuario de Google';
            $user->password = Hash::make(Str::random(32));
        }

        $user->google_access_token = $token['access_token'] ?? null;
        // Google solo envía refresh_token en el primer consentimiento; en re-logins llega
        // null, así que conservamos el que ya tuviera el usuario.
        $user->google_refresh_token = $token['refresh_token'] ?? $user->google_refresh_token;
        $user->google_expires_in = $token['expires_in'] ?? null;
        $user->google_scope = $token['scope'] ?? null;
        $user->google_token_type = $token['token_type'] ?? null;

        $user->save();

        return $user;
    }

    private function buildGoogleClient(): \Google_Client
    {
        $clientId = config('services.youtube_analytics.client_id');
        $clientSecret = config('services.youtube_analytics.client_secret');
        $redirectUri = config('services.youtube_analytics.redirect_uri');

        if (!$clientId || !$clientSecret || !$redirectUri) {
            Log::error('Google OAuth misconfigured', [
                'has_client_id' => (bool) $clientId,
                'has_client_secret' => (bool) $clientSecret,
                'has_redirect_uri' => (bool) $redirectUri,
            ]);

            throw new \RuntimeException('Google OAuth no está configurado correctamente.');
        }

        $client = new \Google_Client();
        $client->setClientId($clientId);
        $client->setClientSecret($clientSecret);
        $client->setRedirectUri($redirectUri);

        $client->setScopes([
            'https://www.googleapis.com/auth/userinfo.email',
            'https://www.googleapis.com/auth/userinfo.profile',
        ]);

        $client->setAccessType('online');

        return $client;
    }
}
