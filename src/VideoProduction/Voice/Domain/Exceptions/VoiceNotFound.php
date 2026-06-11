<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Voice\Domain\Exceptions;

use RuntimeException;

final class VoiceNotFound extends RuntimeException
{
    public static function withId(string $id): self
    {
        return new self("Voice with id '{$id}' not found");
    }
}
