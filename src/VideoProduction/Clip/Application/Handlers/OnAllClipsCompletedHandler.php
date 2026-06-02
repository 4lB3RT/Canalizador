<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Clip\Application\Handlers;

use Helmreel\Shared\Shared\Domain\Events\DomainEvent;
use Helmreel\Shared\Shared\Domain\Events\DomainEventHandler;
use Helmreel\VideoProduction\Clip\Application\UseCases\ComposeShort\ComposeShort;
use Helmreel\VideoProduction\Clip\Application\UseCases\ComposeShort\ComposeShortRequest;
use Helmreel\VideoProduction\Clip\Domain\Events\AllClipsCompleted;

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
