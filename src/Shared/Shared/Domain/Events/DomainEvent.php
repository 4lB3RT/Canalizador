<?php

declare(strict_types=1);

namespace Helmreel\Shared\Shared\Domain\Events;

use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\DateTime;

interface DomainEvent
{
    public function eventName(): string;

    public function occurredAt(): DateTime;

    public function payload(): array;
}
