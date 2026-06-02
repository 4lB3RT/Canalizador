<?php

declare(strict_types = 1);

namespace Helmreel\YouTube\Video\Application\Handlers;

use Helmreel\Shared\Shared\Domain\Events\DomainEvent;
use Helmreel\Shared\Shared\Domain\Events\DomainEventHandler;
use Helmreel\YouTube\Video\Application\UseCases\GenerateShort\GenerateShort;
use Helmreel\YouTube\Video\Application\UseCases\GenerateShort\GenerateShortRequest;
use Helmreel\YouTube\Video\Domain\Events\VideoCreated;
use Helmreel\YouTube\Video\Domain\Exceptions\VideoFragmentationFailed;
use Helmreel\YouTube\Video\Domain\Exceptions\VideoNotFound;
use Helmreel\YouTube\Video\Domain\Exceptions\YouTubeOperationFailed;

final readonly class OnYouTubeVideoCreatedHandler implements DomainEventHandler
{
    public function __construct(
        private GenerateShort $generateShort,
    ) {
    }

    /**
     * @throws VideoFragmentationFailed
     * @throws YouTubeOperationFailed
     * @throws VideoNotFound
     */
    public function handle(DomainEvent $event): void
    {
        assert($event instanceof VideoCreated);

        $this->generateShort->execute(
            new GenerateShortRequest(videoId: $event->videoId())
        );
    }
}
