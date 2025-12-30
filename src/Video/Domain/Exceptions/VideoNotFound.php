<?php

declare(strict_types=1);

namespace Canalizador\Video\Domain\Exceptions;

use Canalizador\Shared\Domain\Exceptions\EntityNotFound;

final class VideoNotFound extends EntityNotFound
{
    public static function withId(string $videoId): self
    {
        return new self("Video not found with ID: {$videoId}");
    }
}
