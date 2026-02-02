<?php

declare(strict_types=1);

namespace Canalizador\Video\Domain\Exceptions;

use Exception;

final class VideoGenerationFailed extends Exception
{
    public const DEFAULT_MESSAGE = 'Video generation failed';

    public function __construct(string $message = self::DEFAULT_MESSAGE, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function default(): self
    {
        return new self(self::DEFAULT_MESSAGE);
    }

    public static function apiError(string $details): self
    {
        return new self('Video generation API error: ' . $details);
    }

    public static function endpointNotFound(string $endpoint): self
    {
        return new self(
            sprintf(
                'Video generation API endpoint not found (%s).',
                $endpoint
            )
        );
    }
}
