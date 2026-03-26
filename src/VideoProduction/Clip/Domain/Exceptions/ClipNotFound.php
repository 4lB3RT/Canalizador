<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Clip\Domain\Exceptions;

use Canalizador\Shared\Domain\Exceptions\EntityNotFound;

final class ClipNotFound extends EntityNotFound
{
    public static function withId(string $clipId): self
    {
        return new self("Clip not found with ID: {$clipId}");
    }
}
