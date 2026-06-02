<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Clip\Application\Handlers;

use Helmreel\Shared\Shared\Domain\Events\DomainEvent;
use Helmreel\Shared\Shared\Domain\Events\DomainEventHandler;
use Helmreel\VideoProduction\Clip\Application\UseCases\CreateClip\CreateClip;
use Helmreel\VideoProduction\Clip\Application\UseCases\CreateClip\CreateClipRequest;
use Helmreel\VideoProduction\Clip\Domain\Events\ClipCompleted;

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
