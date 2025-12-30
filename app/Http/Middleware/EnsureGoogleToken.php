<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

final class EnsureGoogleToken
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'User must be authenticated',
            ], 401);
        }

        if ($user->google_access_token && $user->google_expires_in) {
            $expiresAt = $user->updated_at->addSeconds($user->google_expires_in);

            if ($expiresAt->greaterThanOrEqualTo(now()->setTimezone('Europe/Madrid'))) {
                $this->refreshGoogleToken($user);
            }
        }

        if (!$user->google_access_token) {
            return response()->json([
                'error' => 'Google authentication required',
                'message' => 'You need to authenticate with Google. Please visit /auth/google/login to complete authentication.',
            ], 403);
        }

        return $next($request);
    }

    protected function refreshGoogleToken(User $user): void
    {
        try {
            if (!$user->google_refresh_token) {
                Log::warning('No refresh token available for user', ['user_id' => $user->id]);
                return;
            }

            $client = $this->buildGoogleClient();
            $client->refreshToken($user->google_refresh_token);
            $newToken = $client->getAccessToken();

            if (isset($newToken['access_token'])) {
                $user->update([
                    'google_refresh_token' => $newToken['refresh_token'] ?? $user->google_refresh_token,
                    'google_access_token' => $newToken['access_token'],
                    'google_expires_in' => $newToken['expires_in'] ?? null,
                    'google_scope' => $newToken['scope'] ?? null,
                    'google_token_type' => $newToken['token_type'] ?? null,
                ]);

                $user->refresh();
            }
        } catch (\Exception $e) {
            Log::warning('Failed to refresh Google token', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
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
            'https://www.googleapis.com/auth/youtube.upload',
            'https://www.googleapis.com/auth/youtube',
            'https://www.googleapis.com/auth/userinfo.email',
            'https://www.googleapis.com/auth/userinfo.profile',
        ]);
        $client->setAccessType('offline');

        return $client;
    }
}
