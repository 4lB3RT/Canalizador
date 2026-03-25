<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Shared\Infrastructure\Services;

use Canalizador\VideoProduction\Shared\Domain\Services\Clock;
use Canalizador\VideoProduction\Shared\Domain\ValueObjects\DateTime;

final readonly class SystemClock implements Clock
{
    public function now(): DateTime
    {
        return new DateTime(new \DateTimeImmutable());
    }
}
