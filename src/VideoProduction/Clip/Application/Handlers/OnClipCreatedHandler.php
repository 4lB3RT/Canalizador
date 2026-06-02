<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Clip\Application\Handlers;

use Helmreel\Shared\Shared\Domain\Events\DomainEvent;
use Helmreel\Shared\Shared\Domain\Events\DomainEventHandler;
use Helmreel\VideoProduction\Clip\Application\UseCases\GenerateClip\GenerateClip;
use Helmreel\VideoProduction\Clip\Application\UseCases\GenerateClip\GenerateClipRequest;
use Helmreel\VideoProduction\Clip\Domain\Events\ClipCreated;

final readonly class OnClipCreatedHandler implements DomainEventHandler
{
    public function __construct(
        private GenerateClip $generateClip
    ) {
    }

    public function handle(DomainEvent $event): void
    {
        assert($event instanceof ClipCreated);

        $this->generateClip->execute(
            new GenerateClipRequest(clipId: $event->clipId())
        );
    }
}
