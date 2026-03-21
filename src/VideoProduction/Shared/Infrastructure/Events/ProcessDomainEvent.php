<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Shared\Infrastructure\Events;

use Canalizador\VideoProduction\Shared\Domain\Events\DomainEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class ProcessDomainEvent implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;
    public int $timeout = 300;

    public function __construct(
        public readonly DomainEvent $event
    ) {
        $this->onQueue($event->eventName());
    }

    public function handle(EventHandlerRegistry $registry): void
    {
        $handlers = $registry->handlersFor($this->event);

        foreach ($handlers as $handler) {
            $handler->handle($this->event);
        }
    }
}
