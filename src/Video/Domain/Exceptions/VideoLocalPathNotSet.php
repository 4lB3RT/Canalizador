<?php

declare(strict_types=1);

namespace Canalizador\Video\Domain\Exceptions;

use RuntimeException;

final class VideoLocalPathNotSet extends RuntimeException
{
    public static function forVideoId(string $videoId): self
    {
        return new self("Video local path is not set for video ID: {$videoId}");
    }
}
