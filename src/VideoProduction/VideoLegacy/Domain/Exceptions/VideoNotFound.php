<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\VideoLegacy\Domain\Exceptions;

use Canalizador\VideoProduction\Shared\Domain\Exceptions\EntityNotFound;

final class VideoNotFound extends EntityNotFound
{
    public const DEFAULT_MESSAGE = 'Video not found';

    public function __construct(string $message = self::DEFAULT_MESSAGE)
    {
        parent::__construct($message);
    }

    public static function default(): self
    {
        return new self(self::DEFAULT_MESSAGE);
    }
}
