<?php

declare(strict_types=1);

namespace Helmreel\Shared\Media\Domain\Exceptions;

use RuntimeException;

final class MediaNotFound extends RuntimeException
{
    public static function withId(string $id): self
    {
        return new self("Media with id '{$id}' not found");
    }
}
