<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Google_Client;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class EnsureGoogleToken
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        if (!$user || !$user->google_access_token) {
            $client = new Google_Client();
            $client->setClientId(config('services.youtube_analytics.client_id'));
            $client->setClientSecret(config('services.youtube_analytics.client_secret'));
            $client->setRedirectUri(config('services.youtube_analytics.redirect_uri'));
            $client->setScopes(['https://www.googleapis.com/auth/yt-analytics.readonly', 'https://www.googleapis.com/auth/userinfo.email', 'https://www.googleapis.com/auth/userinfo.profile']);
            $client->setAccessType('offline');

            if ($request->has('code')) {
                $token = $client->fetchAccessTokenWithAuthCode($request->get('code'));
                $client->setAccessToken($token);

                // Fetch user info from Google
                $oauth2 = new \Google_Service_Oauth2($client);
                $googleUser = $oauth2->userinfo->get();

                // Find or create user
                $user = User::updateOrCreate(
                    ['email' => $googleUser->email],
                    [
                        'name' => $googleUser->name,
                        'email' => $googleUser->email,
                        'password' => 1234,
                        'google_access_token' => $token['access_token'] ?? null,
                        'google_refresh_token' => $token['refresh_token'] ?? null,
                        'google_expires_in' => $token['expires_in'] ?? null,
                        'google_scope' => $token['scope'] ?? null,
                        'google_token_type' => $token['token_type'] ?? null,
                    ]
                );
                Auth::login($user);
                // Store token in session from authenticated user
                session(['google_access_token' => $user->google_access_token]);
            }

            if (!$request->has('code')) {
                $authUrl = $client->createAuthUrl();
                return redirect($authUrl);
            }
        }

        return $next($request);
    }
}
