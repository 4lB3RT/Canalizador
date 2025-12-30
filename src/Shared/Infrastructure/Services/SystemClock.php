<?php

declare(strict_types=1);

namespace Canalizador\Shared\Infrastructure\Services;

use Canalizador\Shared\Domain\Services\Clock;
use Canalizador\Shared\Domain\ValueObjects\DateTime;

final readonly class SystemClock implements Clock
{
    public function now(): DateTime
    {
        return new DateTime(new \DateTimeImmutable());
    }
}
