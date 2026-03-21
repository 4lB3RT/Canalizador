<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Domain\Exceptions;

use Exception;

final class YouTubeOperationFailed extends Exception
{
    public static function apiError(string $details): self
    {
        return new self('YouTube operation failed: ' . $details);
    }

    public static function videoNotFound(string $videoId): self
    {
        return new self("YouTube video not found: {$videoId}");
    }

    public static function channelNotFound(string $channelId): self
    {
        return new self("YouTube channel not found: {$channelId}");
    }
}
