<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Clip\Application\Handlers;

use Canalizador\VideoProduction\Clip\Application\UseCases\GenerateClip\GenerateClip;
use Canalizador\VideoProduction\Clip\Application\UseCases\GenerateClip\GenerateClipRequest;
use Canalizador\VideoProduction\Clip\Domain\Events\ClipCreated;
use Canalizador\VideoProduction\Shared\Domain\Events\DomainEvent;
use Canalizador\VideoProduction\Shared\Domain\Events\DomainEventHandler;

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
