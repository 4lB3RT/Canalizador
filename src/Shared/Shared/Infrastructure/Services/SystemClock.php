<?php

declare(strict_types=1);

namespace Helmreel\Shared\Shared\Infrastructure\Services;

use Helmreel\Shared\Shared\Domain\Services\Clock;
use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\DateTime;

final readonly class SystemClock implements Clock
{
    public function now(): DateTime
    {
        return new DateTime(new \DateTimeImmutable());
    }
}
