<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Clip\Application\Handlers;

use Canalizador\Shared\Shared\Domain\Events\DomainEvent;
use Canalizador\Shared\Shared\Domain\Events\DomainEventHandler;
use Canalizador\VideoProduction\Clip\Application\UseCases\CreateClip\CreateClip;
use Canalizador\VideoProduction\Clip\Application\UseCases\CreateClip\CreateClipRequest;
use Canalizador\VideoProduction\Video\Domain\Events\VideoCreated;

final readonly class OnVideoCreatedHandler implements DomainEventHandler
{
    public function __construct(
        private CreateClip $createClip
    ) {
    }

    public function handle(DomainEvent $event): void
    {
        assert($event instanceof VideoCreated);

        $this->createClip->execute(
            new CreateClipRequest(videoId: $event->videoId())
        );
    }
}
