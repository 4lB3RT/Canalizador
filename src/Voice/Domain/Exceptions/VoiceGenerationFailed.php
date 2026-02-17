<?php

declare(strict_types=1);

namespace Canalizador\Voice\Domain\Exceptions;

use Exception;

final class VoiceGenerationFailed extends Exception
{
    public const DEFAULT_MESSAGE = 'Voice generation failed';

    public function __construct(string $message = self::DEFAULT_MESSAGE, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function apiError(string $details): self
    {
        return new self('Voice generation API error: ' . $details);
    }
}
