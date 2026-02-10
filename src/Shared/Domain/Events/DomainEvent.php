<?php

declare(strict_types=1);

namespace Canalizador\Shared\Domain\Events;

use Canalizador\Shared\Domain\ValueObjects\DateTime;

interface DomainEvent
{
    public function eventName(): string;

    public function occurredAt(): DateTime;

    public function payload(): array;
}
