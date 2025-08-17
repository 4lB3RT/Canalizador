<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureGoogleToken
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user && $user->google_expires_in) {
            $expiresAt = $user->updated_at->addSeconds($user->google_expires_in);
            if (now()->greaterThanOrEqualTo($expiresAt)) {
                $this->refreshGoogleToken($user);
            }
        }

        if (!$user || !$user->google_access_token) {
            return $this->handleGoogleOAuth($request);
        }

        return $next($request);
    }

    protected function refreshGoogleToken($user): void
    {
        $client = $this->buildGoogleClient();
        $client->refreshToken($user->google_access_token);
        $newToken = $client->getAccessToken();
        $user->fill([
            'google_refresh_token' => $newToken['refresh_token'] ?? $user->google_refresh_token,
            'google_access_token'  => $newToken['access_token']  ?? null,
            'google_expires_in'    => $newToken['expires_in']    ?? null,
            'google_scope'         => $newToken['scope']         ?? null,
            'google_token_type'    => $newToken['token_type']    ?? null,
        ])->save();
    }

    protected function handleGoogleOAuth(Request $request)
    {
        $client = $this->buildGoogleClient();
        if ($request->has('code')) {
            $token = $client->fetchAccessTokenWithAuthCode($request->get('code'));
            $client->setAccessToken($token);
            $oauth2     = new \Google_Service_Oauth2($client);
            $googleUser = $oauth2->userinfo->get();
            $user       = User::updateOrCreate(
                ['email' => $googleUser->email],
                [
                    'name'                 => $googleUser->name,
                    'email'                => $googleUser->email,
                    'password'             => 1234,
                    'google_access_token'  => $token['access_token']  ?? null,
                    'google_refresh_token' => $token['refresh_token'] ?? null,
                    'google_expires_in'    => $token['expires_in']    ?? null,
                    'google_scope'         => $token['scope']         ?? null,
                    'google_token_type'    => $token['token_type']    ?? null,
                ]
            );
            Auth::login($user);
            session(['google_access_token' => $user->google_access_token]);
        } else {
            return redirect($client->createAuthUrl());
        }
    }

    protected function buildGoogleClient(): \Google_Client
    {
        $client = new \Google_Client();
        $client->setClientId(config('services.youtube_analytics.client_id'));
        $client->setClientSecret(config('services.youtube_analytics.client_secret'));
        $client->setRedirectUri(config('services.youtube_analytics.redirect_uri'));
        $client->setScopes([
            'https://www.googleapis.com/auth/yt-analytics.readonly',
            'https://www.googleapis.com/auth/userinfo.email',
            'https://www.googleapis.com/auth/userinfo.profile',
        ]);
        $client->setAccessType('offline');

        return $client;
    }
}
