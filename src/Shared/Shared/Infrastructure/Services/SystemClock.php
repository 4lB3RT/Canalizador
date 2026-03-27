<?php

declare(strict_types=1);

namespace Canalizador\Shared\Shared\Infrastructure\Services;

use Canalizador\Shared\Shared\Domain\Services\Clock;
use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\DateTime;

final readonly class SystemClock implements Clock
{
    public function now(): DateTime
    {
        return new DateTime(new \DateTimeImmutable());
    }
}
