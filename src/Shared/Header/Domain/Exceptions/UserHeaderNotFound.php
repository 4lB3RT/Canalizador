<?php

declare(strict_types=1);

namespace Canalizador\Shared\Header\Domain\Exceptions;

use Canalizador\Shared\Shared\Domain\Exceptions\EntityNotFound;

final class UserHeaderNotFound extends EntityNotFound
{
    public static function withId(int $userId): self
    {
        return new self("User not found with ID: {$userId}");
    }
}
