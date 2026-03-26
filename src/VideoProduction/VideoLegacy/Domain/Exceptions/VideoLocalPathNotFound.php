<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\VideoLegacy\Domain\Exceptions;

use Canalizador\Shared\Domain\Exceptions\EntityNotFound;

final class VideoLocalPathNotFound extends EntityNotFound
{
    public const DEFAULT_MESSAGE = 'Video local path not found';

    public function __construct(string $message = self::DEFAULT_MESSAGE)
    {
        parent::__construct($message);
    }

    public static function default(): self
    {
        return new self(self::DEFAULT_MESSAGE);
    }
}
