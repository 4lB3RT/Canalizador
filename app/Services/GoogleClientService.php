<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Helmreel\YouTube\Channel\Domain\ValueObjects\ChannelId;
use Helmreel\YouTube\Channel\Infrastructure\DAO\ChannelDAO;
use DateTimeImmutable;
use Google_Client;
use Illuminate\Support\Facades\Auth;

final class GoogleClientService
{
    public function __construct(
        private readonly GoogleTokenService $googleTokenService,
    ) {
    }

    public function buildClient(array $scopes = [], ?int $userId = null): Google_Client
    {
        $userId ??= Auth::id();

        $client = new Google_Client();
        $client->setClientId(config('services.youtube_analytics.client_id'));
        $client->setClientSecret(config('services.youtube_analytics.client_secret'));
        $client->setRedirectUri(config('services.youtube_analytics.redirect_uri', 'http://localhost:8010/auth/google/callback'));
        $client->setAccessType('offline');

        if (empty($scopes)) {
            $scopes = [
                'https://www.googleapis.com/auth/youtube.upload',
                'https://www.googleapis.com/auth/youtube',
                'https://www.googleapis.com/auth/userinfo.email',
                'https://www.googleapis.com/auth/userinfo.profile',
            ];
        }

        $client->setScopes($scopes);

        $accessToken = $this->googleTokenService->getAccessToken($userId);
        if ($accessToken) {
            $client->setAccessToken($accessToken);
        }

        if ($client->isAccessTokenExpired()) {
            $refreshToken = $this->googleTokenService->getRefreshToken($userId);
            if ($refreshToken && $userId !== null) {
                try {
                    $client->refreshToken($refreshToken);
                    $newToken = $client->getAccessToken();

                    if (isset($newToken['access_token'])) {
                        $this->persistRefreshedToken($userId, $newToken);
                    }
                } catch (\Exception $e) {
                    throw new \RuntimeException('Failed to refresh Google token. Please re-authenticate.', 0, $e);
                }
            }
        }

        return $client;
    }

    public function buildYouTubeClient(?int $userId = null): Google_Client
    {
        return $this->buildClient([
            'https://www.googleapis.com/auth/youtube.upload',
            'https://www.googleapis.com/auth/youtube',
        ], $userId);
    }

    public function buildYouTubeClientForChannel(ChannelId $channelId): Google_Client
    {
        $channel = ChannelDAO::where('id', $channelId->value())->first();

        if ($channel === null || !$channel->access_token) {
            throw new \RuntimeException(
                'Channel ' . $channelId->value() . ' has no Google token. Please link the channel.',
            );
        }

        $client = new Google_Client();
        $client->setClientId(config('services.youtube_analytics.client_id'));
        $client->setClientSecret(config('services.youtube_analytics.client_secret'));
        $client->setRedirectUri(config('services.youtube_analytics.redirect_uri', 'http://localhost:8010/auth/google/callback'));
        $client->setAccessType('offline');
        $client->setScopes([
            'https://www.googleapis.com/auth/youtube.upload',
            'https://www.googleapis.com/auth/youtube',
        ]);

        $refreshToken = $channel->refresh_token ? (string) $channel->refresh_token : null;

        $client->setAccessToken([
            'access_token'  => (string) $channel->access_token,
            'refresh_token' => $refreshToken,
            'expires_in'    => $channel->token_expires_at
                ? max(0, $channel->token_expires_at->getTimestamp() - (new DateTimeImmutable())->getTimestamp())
                : null,
            'scope'         => $channel->token_scope,
            'token_type'    => $channel->token_type ?? 'Bearer',
        ]);

        if ($client->isAccessTokenExpired() && $refreshToken) {
            try {
                $client->refreshToken($refreshToken);
                $newToken = $client->getAccessToken();

                if (isset($newToken['access_token'])) {
                    $expiresAt = isset($newToken['expires_in'])
                        ? (new DateTimeImmutable())->modify('+' . (int) $newToken['expires_in'] . ' seconds')
                        : null;

                    $channel->update([
                        'access_token'     => (string) $newToken['access_token'],
                        'refresh_token'    => (string) ($newToken['refresh_token'] ?? $refreshToken),
                        'token_expires_at' => $expiresAt,
                        'token_scope'      => (string) ($newToken['scope']      ?? $channel->token_scope),
                        'token_type'       => (string) ($newToken['token_type'] ?? $channel->token_type ?? 'Bearer'),
                    ]);
                }
            } catch (\Exception $e) {
                throw new \RuntimeException(
                    'Failed to refresh Google token for channel ' . $channelId->value() . '. Please re-link the channel.',
                    0,
                    $e,
                );
            }
        }

        return $client;
    }

    public function buildYouTubeAnalyticsClient(?int $userId = null): Google_Client
    {
        return $this->buildClient([
            'https://www.googleapis.com/auth/youtube',
        ], $userId);
    }

    private function persistRefreshedToken(int $userId, array $newToken): void
    {
        $user = User::find($userId);

        if ($user === null) {
            return;
        }

        $payload = [
            'google_access_token'  => $newToken['access_token'],
            'google_refresh_token' => $newToken['refresh_token'] ?? $user->google_refresh_token,
            'google_expires_in'    => $newToken['expires_in']    ?? null,
            'google_scope'         => $newToken['scope']         ?? null,
            'google_token_type'    => $newToken['token_type']    ?? null,
        ];

        $user->update($payload);

        $this->googleTokenService->storeToken($userId, [
            'access_token'  => $payload['google_access_token'],
            'refresh_token' => $payload['google_refresh_token'],
            'expires_in'    => $payload['google_expires_in'],
            'scope'         => $payload['google_scope'],
            'token_type'    => $payload['google_token_type'],
        ]);
    }
}
