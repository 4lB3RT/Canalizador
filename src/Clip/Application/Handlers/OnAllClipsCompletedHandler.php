<?php

declare(strict_types=1);

namespace Canalizador\Clip\Application\Handlers;

use Canalizador\Clip\Application\UseCases\ComposeShort\ComposeShort;
use Canalizador\Clip\Application\UseCases\ComposeShort\ComposeShortRequest;
use Canalizador\Clip\Domain\Events\AllClipsCompleted;
use Canalizador\Shared\Domain\Events\DomainEvent;
use Canalizador\Shared\Domain\Events\DomainEventHandler;

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
