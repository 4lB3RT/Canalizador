<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Clip\Application\Handlers;

use Canalizador\VideoProduction\Clip\Application\UseCases\CreateClip\CreateClip;
use Canalizador\VideoProduction\Clip\Application\UseCases\CreateClip\CreateClipRequest;
use Canalizador\VideoProduction\Clip\Domain\Events\ClipCompleted;
use Canalizador\VideoProduction\Shared\Domain\Events\DomainEvent;
use Canalizador\VideoProduction\Shared\Domain\Events\DomainEventHandler;

final readonly class OnClipCompletedHandler implements DomainEventHandler
{
    public function __construct(
        private CreateClip $createClip
    ) {
    }

    public function handle(DomainEvent $event): void
    {
        assert($event instanceof ClipCompleted);

        $this->createClip->execute(
            new CreateClipRequest(videoId: $event->videoId())
        );
    }
}
