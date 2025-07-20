<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;

class GoogleTokenService
{
    public function getAccessToken(): ?string
    {
        $user = Auth::user();
        return $user ? $user->google_access_token : null;
    }

    public function getRefreshToken(): ?string
    {
        $user = Auth::user();
        return $user ? $user->google_refresh_token : null;
    }
}

