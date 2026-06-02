<?php

declare(strict_types = 1);

namespace Helmreel\Shared\Shared\Domain;

use Helmreel\Shared\Shared\Domain\Events\DomainEvent;

abstract class AggregateRoot
{
    private array $events = [];

    protected function recordEvent(DomainEvent $event): void
    {
        $this->events[] = $event;
    }

    public function releaseEvents(): array
    {
        $events       = $this->events;
        $this->events = [];

        return $events;
    }
}
