<?php

declare(strict_types=1);

namespace Canalizador\Shared\Shared\Domain\Services;

interface PasswordHasher
{
    public function hash(string $plain): string;

    public function check(string $plain, string $hashed): bool;
}
