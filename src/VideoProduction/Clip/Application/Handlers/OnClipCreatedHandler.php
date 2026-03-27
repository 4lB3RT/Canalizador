<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Clip\Application\Handlers;

use Canalizador\Shared\Shared\Domain\Events\DomainEvent;
use Canalizador\Shared\Shared\Domain\Events\DomainEventHandler;
use Canalizador\VideoProduction\Clip\Application\UseCases\GenerateClip\GenerateClip;
use Canalizador\VideoProduction\Clip\Application\UseCases\GenerateClip\GenerateClipRequest;
use Canalizador\VideoProduction\Clip\Domain\Events\ClipCreated;

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
