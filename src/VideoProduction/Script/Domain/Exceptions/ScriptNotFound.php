<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Script\Domain\Exceptions;

use Exception;

final class ScriptNotFound extends Exception
{
    public const DEFAULT_MESSAGE = 'Script not found';

    public function __construct(string $message = self::DEFAULT_MESSAGE, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function default(): self
    {
        return new self(self::DEFAULT_MESSAGE);
    }

    public static function withId(string $scriptId): self
    {
        return new self(sprintf('Script not found with ID: %s', $scriptId));
    }
}
