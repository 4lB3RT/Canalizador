<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Video\Domain\Exceptions;

use Helmreel\Shared\Shared\Domain\Exceptions\EntityNotFound;

final class VideoNotFound extends EntityNotFound
{
    public static function withId(string $videoId): self
    {
        return new self("Video not found with ID: {$videoId}");
    }
}
