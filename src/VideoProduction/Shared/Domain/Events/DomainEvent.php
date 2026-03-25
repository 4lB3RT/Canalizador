<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Shared\Domain\Events;

use Canalizador\VideoProduction\Shared\Domain\ValueObjects\DateTime;

interface DomainEvent
{
    public function eventName(): string;

    public function occurredAt(): DateTime;

    public function payload(): array;
}
