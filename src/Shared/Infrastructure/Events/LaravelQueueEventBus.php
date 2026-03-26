<?php

declare(strict_types=1);

namespace Canalizador\Shared\Infrastructure\Events;

use Canalizador\Shared\Domain\Events\DomainEvent;
use Canalizador\Shared\Domain\Events\EventBus;

final readonly class LaravelQueueEventBus implements EventBus
{
    public function publish(DomainEvent ...$events): void
    {
        foreach ($events as $event) {
            dispatch(new ProcessDomainEvent($event));
        }
    }
}
