<?php

declare(strict_types=1);

namespace Helmreel\Shared\Shared\Domain\Events;

interface DomainEventHandler
{
    public function handle(DomainEvent $event): void;
}
