<?php

declare(strict_types=1);

namespace App\Services;

use Google_Client;
use Illuminate\Support\Facades\Auth;

final class GoogleClientService
{
    public function __construct(
        private readonly GoogleTokenService $googleTokenService
    ) {
    }

    /**
     * Build a Google Client with authenticated user's token.
     * Automatically refreshes token if expired.
     */
    public function buildClient(array $scopes = []): Google_Client
    {
        $client = new Google_Client();
        $client->setClientId(config('services.youtube_analytics.client_id'));
        $client->setClientSecret(config('services.youtube_analytics.client_secret'));
        $client->setRedirectUri(config('services.youtube_analytics.redirect_uri', 'http://localhost:8010/auth/google/callback'));
        $client->setAccessType('offline');

        // Use default scopes if none provided
        if (empty($scopes)) {
            $scopes = [
                'https://www.googleapis.com/auth/yt-analytics.readonly',
                'https://www.googleapis.com/auth/youtube.upload',
                'https://www.googleapis.com/auth/youtube',
                'https://www.googleapis.com/auth/userinfo.email',
                'https://www.googleapis.com/auth/userinfo.profile',
            ];
        }

        $client->setScopes($scopes);

        // Set access token from authenticated user
        $accessToken = $this->googleTokenService->getAccessToken();
        if ($accessToken) {
            $client->setAccessToken($accessToken);
        }

        // Refresh token if expired
        if ($client->isAccessTokenExpired()) {
            $refreshToken = $this->googleTokenService->getRefreshToken();
            if ($refreshToken) {
                try {
                    $client->refreshToken($refreshToken);
                    $newToken = $client->getAccessToken();

                    // Update user's token in database
                    $user = Auth::user();
                    if ($user && isset($newToken['access_token'])) {
                        $user->update([
                            'google_access_token' => $newToken['access_token'],
                            'google_refresh_token' => $newToken['refresh_token'] ?? $user->google_refresh_token,
                            'google_expires_in' => $newToken['expires_in'] ?? null,
                            'google_scope' => $newToken['scope'] ?? null,
                            'google_token_type' => $newToken['token_type'] ?? null,
                        ]);
                    }
                } catch (\Exception $e) {
                    // Token refresh failed, will need to re-authenticate
                    throw new \RuntimeException('Failed to refresh Google token. Please re-authenticate.', 0, $e);
                }
            }
        }

        return $client;
    }

    /**
     * Build a Google Client for YouTube API.
     */
    public function buildYouTubeClient(): Google_Client
    {
        return $this->buildClient([
            'https://www.googleapis.com/auth/youtube.upload',
            'https://www.googleapis.com/auth/youtube',
        ]);
    }

    /**
     * Build a Google Client for YouTube Analytics API.
     */
    public function buildYouTubeAnalyticsClient(): Google_Client
    {
        return $this->buildClient([
            'https://www.googleapis.com/auth/yt-analytics.readonly',
            'https://www.googleapis.com/auth/youtube',
        ]);
    }
}
