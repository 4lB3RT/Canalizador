<?php

declare(strict_types=1);

namespace Helmreel\Shared\Profile\Domain\Exceptions;

use Helmreel\Shared\Shared\Domain\Exceptions\EntityNotFound;

final class ProfileNotFound extends EntityNotFound
{
    public static function withId(int $userId): self
    {
        return new self("Profile not found for user ID: {$userId}");
    }
}
