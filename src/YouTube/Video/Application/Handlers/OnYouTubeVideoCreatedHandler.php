<?php

declare(strict_types = 1);

namespace Canalizador\YouTube\Video\Application\Handlers;

use Canalizador\Shared\Shared\Domain\Events\DomainEvent;
use Canalizador\Shared\Shared\Domain\Events\DomainEventHandler;
use Canalizador\YouTube\Video\Application\UseCases\GenerateShorts\GenerateShorts;
use Canalizador\YouTube\Video\Application\UseCases\GenerateShorts\GenerateShortsRequest;
use Canalizador\YouTube\Video\Domain\Events\VideoCreated;

final readonly class OnYouTubeVideoCreatedHandler implements DomainEventHandler
{
    public function __construct(
        private GenerateShorts $generateShorts,
    ) {
    }

    public function handle(DomainEvent $event): void
    {
        assert($event instanceof VideoCreated);

        $this->generateShorts->execute(
            new GenerateShortsRequest(videoYoutubeId: $event->platformId())
        );
    }
}
