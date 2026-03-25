<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Clip\Application\Handlers;

use Canalizador\VideoProduction\Clip\Application\UseCases\ComposeShort\ComposeShort;
use Canalizador\VideoProduction\Clip\Application\UseCases\ComposeShort\ComposeShortRequest;
use Canalizador\VideoProduction\Clip\Domain\Events\AllClipsCompleted;
use Canalizador\VideoProduction\Shared\Domain\Events\DomainEvent;
use Canalizador\VideoProduction\Shared\Domain\Events\DomainEventHandler;

final readonly class OnAllClipsCompletedHandler implements DomainEventHandler
{
    public function __construct(
        private ComposeShort $composeShort
    ) {
    }

    public function handle(DomainEvent $event): void
    {
        assert($event instanceof AllClipsCompleted);

        $this->composeShort->execute(
            new ComposeShortRequest(videoId: $event->videoId())
        );
    }
}
