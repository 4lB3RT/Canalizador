<?php

declare(strict_types = 1);

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class GoogleTokenService
{
    private const string CACHE_KEY_PREFIX = 'google_token:';

    private const int FALLBACK_TTL_SECONDS = 3600;

    public function getAccessToken(?int $userId = null): ?string
    {
        return $this->loadToken($userId)['access_token'] ?? null;
    }

    public function getRefreshToken(?int $userId = null): ?string
    {
        return $this->loadToken($userId)['refresh_token'] ?? null;
    }

    public function storeToken(int $userId, array $token): void
    {
        $payload = [
            'access_token'  => $token['access_token']  ?? null,
            'refresh_token' => $token['refresh_token'] ?? null,
            'expires_in'    => $token['expires_in']    ?? null,
            'scope'         => $token['scope']         ?? null,
            'token_type'    => $token['token_type']    ?? null,
        ];

        Cache::put(
            self::CACHE_KEY_PREFIX . $userId,
            $payload,
            (int) ($payload['expires_in'] ?? self::FALLBACK_TTL_SECONDS)
        );
    }

    public function forget(int $userId): void
    {
        Cache::forget(self::CACHE_KEY_PREFIX . $userId);
    }

    private function loadToken(?int $userId): ?array
    {
        $userId ??= Auth::id();

        if ($userId === null) {
            return null;
        }

        $cached = Cache::get(self::CACHE_KEY_PREFIX . $userId);

        if (is_array($cached) && !empty($cached['access_token'])) {
            return $cached;
        }

        $user = User::find($userId);

        if ($user === null || !$user->google_access_token) {
            return null;
        }

        $token = [
            'access_token'  => $user->google_access_token,
            'refresh_token' => $user->google_refresh_token,
            'expires_in'    => $user->google_expires_in,
            'scope'         => $user->google_scope,
            'token_type'    => $user->google_token_type,
        ];

        $this->storeToken($userId, $token);

        return $token;
    }
}
