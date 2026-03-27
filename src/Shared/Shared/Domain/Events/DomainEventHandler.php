<?php

declare(strict_types=1);

namespace Canalizador\Shared\Shared\Domain\Events;

interface DomainEventHandler
{
    public function handle(DomainEvent $event): void;
}
