<?php

declare(strict_types=1);

namespace Canalizador\Video\Application\Handlers;

use Canalizador\Shared\Domain\Events\DomainEvent;
use Canalizador\Shared\Domain\Events\DomainEventHandler;
use Canalizador\Video\Application\UseCases\CreateClip\CreateClip;
use Canalizador\Video\Application\UseCases\CreateClip\CreateClipRequest;
use Canalizador\Video\Domain\Events\VideoCreated;

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
