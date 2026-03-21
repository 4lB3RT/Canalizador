<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Domain\Exceptions;

use Exception;

final class VideoNotFound extends Exception
{
    public static function withId(string $id): self
    {
        return new self("YouTube video not found: {$id}");
    }
}
