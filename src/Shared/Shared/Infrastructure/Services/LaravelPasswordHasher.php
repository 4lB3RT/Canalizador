<?php

declare(strict_types=1);

namespace Helmreel\Shared\Shared\Infrastructure\Services;

use Helmreel\Shared\Shared\Domain\Services\PasswordHasher;
use Illuminate\Contracts\Hashing\Hasher;

final readonly class LaravelPasswordHasher implements PasswordHasher
{
    public function __construct(private Hasher $hasher)
    {
    }

    public function hash(string $plain): string
    {
        return $this->hasher->make($plain);
    }

    public function check(string $plain, string $hashed): bool
    {
        return $this->hasher->check($plain, $hashed);
    }
}
