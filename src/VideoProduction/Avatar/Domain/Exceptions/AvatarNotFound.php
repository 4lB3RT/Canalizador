<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Avatar\Domain\Exceptions;

use RuntimeException;

final class AvatarNotFound extends RuntimeException
{
    public static function withId(string $id): self
    {
        return new self("Avatar with id '{$id}' not found");
    }
}

