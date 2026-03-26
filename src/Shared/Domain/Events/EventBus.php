<?php

declare(strict_types=1);

namespace Canalizador\Shared\Domain\Events;

interface EventBus
{
    public function publish(DomainEvent ...$events): void;
}
