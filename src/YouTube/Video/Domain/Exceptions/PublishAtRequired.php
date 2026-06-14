<?php

declare(strict_types=1);

namespace Helmreel\YouTube\Video\Domain\Exceptions;

use RuntimeException;

final class PublishAtRequired extends RuntimeException
{
    public static function forScheduledVideo(string $videoId): self
    {
        return new self("A publish date is required to schedule video ID: {$videoId}");
    }
}
