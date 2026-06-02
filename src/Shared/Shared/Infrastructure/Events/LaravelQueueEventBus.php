<?php

declare(strict_types=1);

namespace Helmreel\Shared\Shared\Infrastructure\Events;

use Helmreel\Shared\Shared\Domain\Events\DomainEvent;
use Helmreel\Shared\Shared\Domain\Events\EventBus;

final readonly class LaravelQueueEventBus implements EventBus
{
    public function publish(DomainEvent ...$events): void
    {
        foreach ($events as $event) {
            dispatch(new ProcessDomainEvent($event));
        }
    }
}
