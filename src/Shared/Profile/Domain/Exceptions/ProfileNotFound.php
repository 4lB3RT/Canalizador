<?php

declare(strict_types=1);

namespace Canalizador\Shared\Profile\Domain\Exceptions;

use Canalizador\Shared\Shared\Domain\Exceptions\EntityNotFound;

final class ProfileNotFound extends EntityNotFound
{
    public static function withId(int $userId): self
    {
        return new self("Profile not found for user ID: {$userId}");
    }
}
