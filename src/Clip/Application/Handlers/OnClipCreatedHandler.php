<?php

declare(strict_types=1);

namespace Canalizador\Clip\Application\Handlers;

use Canalizador\Clip\Application\UseCases\GenerateClip\GenerateClip;
use Canalizador\Clip\Application\UseCases\GenerateClip\GenerateClipRequest;
use Canalizador\Clip\Domain\Events\ClipCreated;
use Canalizador\Shared\Domain\Events\DomainEvent;
use Canalizador\Shared\Domain\Events\DomainEventHandler;

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
