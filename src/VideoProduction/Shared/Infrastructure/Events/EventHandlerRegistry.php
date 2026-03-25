<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Shared\Infrastructure\Events;

use Canalizador\VideoProduction\Shared\Domain\Events\DomainEvent;
use Canalizador\VideoProduction\Shared\Domain\Events\DomainEventHandler;
use Illuminate\Contracts\Container\Container;

final class EventHandlerRegistry
{
    /** @var array<class-string<DomainEvent>, list<class-string<DomainEventHandler>>> */
    private array $handlers = [];

    public function __construct(private readonly Container $container)
    {
    }

    /**
     * @param class-string<DomainEvent>        $eventClass
     * @param class-string<DomainEventHandler> $handlerClass
     */
    public function register(string $eventClass, string $handlerClass): void
    {
        $this->handlers[$eventClass][] = $handlerClass;
    }

    /** @return list<class-string<DomainEvent>> */
    public function registeredEventClasses(): array
    {
        return array_keys($this->handlers);
    }

    /** @return list<DomainEventHandler> */
    public function handlersFor(DomainEvent $event): array
    {
        $handlerClasses = $this->handlers[get_class($event)] ?? [];

        return array_map(
            fn (string $class) => $this->container->make($class),
            $handlerClasses
        );
    }
}
