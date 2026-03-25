<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Shared\Infrastructure\Events;

use Canalizador\VideoProduction\Shared\Domain\Events\DomainEvent;
use Canalizador\VideoProduction\Shared\Domain\Events\EventBus;

final readonly class LaravelQueueEventBus implements EventBus
{
    public function publish(DomainEvent ...$events): void
    {
        foreach ($events as $event) {
            dispatch(new ProcessDomainEvent($event));
        }
    }
}
