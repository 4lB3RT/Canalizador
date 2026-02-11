<?php

declare(strict_types=1);

namespace Canalizador\Video\Application\Handlers;

use Canalizador\Shared\Domain\Events\DomainEvent;
use Canalizador\Shared\Domain\Events\DomainEventHandler;
use Canalizador\Video\Application\UseCases\ComposeShort\ComposeShort;
use Canalizador\Video\Application\UseCases\ComposeShort\ComposeShortRequest;
use Canalizador\Video\Domain\Events\AllClipsCompleted;

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
