<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Image\Domain\Exceptions;

use RuntimeException;

final class ImageNotFound extends RuntimeException
{
    public static function withId(string $id): self
    {
        return new self("Image with id '{$id}' not found");
    }
}
