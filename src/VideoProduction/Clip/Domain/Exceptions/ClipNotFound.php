<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Clip\Domain\Exceptions;

use Helmreel\Shared\Shared\Domain\Exceptions\EntityNotFound;

final class ClipNotFound extends EntityNotFound
{
    public static function withId(string $clipId): self
    {
        return new self("Clip not found with ID: {$clipId}");
    }
}
