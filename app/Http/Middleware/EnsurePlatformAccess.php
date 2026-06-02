<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class EnsurePlatformAccess
{
    private const REALM = 'Helmreel';

    private const ALLOWLIST_PATHS = [
        'api/health-check',
        'up',
        '.well-known/acme-challenge/*',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->isAllowlisted($request)) {
            return $next($request);
        }

        $header = $request->header('Authorization', '');

        if (! str_starts_with($header, 'Basic ')) {
            return $this->challenge();
        }

        $decoded = base64_decode(substr($header, 6), true);

        if ($decoded === false || ! str_contains($decoded, ':')) {
            return $this->challenge();
        }

        [$email, $password] = explode(':', $decoded, 2);

        $user = User::where('email', $email)->first();

        if ($user === null || $user->password === null) {
            return $this->challenge();
        }

        if (! Hash::check($password, $user->password)) {
            return $this->challenge();
        }

        return $next($request);
    }

    private function isAllowlisted(Request $request): bool
    {
        $path = trim($request->path(), '/');

        foreach (self::ALLOWLIST_PATHS as $pattern) {
            if (str_ends_with($pattern, '/*')) {
                $prefix = substr($pattern, 0, -2);
                if (str_starts_with($path, $prefix)) {
                    return true;
                }
            } elseif ($path === $pattern) {
                return true;
            }
        }

        return false;
    }

    private function challenge(): Response
    {
        return response('Authentication required', 401, [
            'WWW-Authenticate' => sprintf('Basic realm="%s", charset="UTF-8"', self::REALM),
        ]);
    }
}
