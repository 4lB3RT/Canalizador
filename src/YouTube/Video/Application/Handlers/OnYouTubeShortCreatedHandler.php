<?php

declare(strict_types = 1);

namespace Canalizador\YouTube\Video\Application\Handlers;

use Canalizador\Shared\Shared\Domain\Events\DomainEvent;
use Canalizador\Shared\Shared\Domain\Events\DomainEventHandler;
use Canalizador\YouTube\Video\Application\UseCases\GenerateShort\GenerateShort;
use Canalizador\YouTube\Video\Application\UseCases\GenerateShort\GenerateShortRequest;
use Canalizador\YouTube\Video\Application\UseCases\PublishVideo\PublishVideo;
use Canalizador\YouTube\Video\Application\UseCases\PublishVideo\PublishVideoRequest;
use Canalizador\YouTube\Video\Domain\Events\ShortCreated;
use Canalizador\YouTube\Video\Domain\Exceptions\VideoFragmentationFailed;
use Canalizador\YouTube\Video\Domain\Exceptions\VideoNotFound;
use Canalizador\YouTube\Video\Domain\Exceptions\YouTubeOperationFailed;

final readonly class OnYouTubeShortCreatedHandler implements DomainEventHandler
{
    public function __construct(
        private GenerateShort $generateShort,
        private PublishVideo  $publishVideo,
    ) {
    }

    /**
     * @throws VideoFragmentationFailed
     * @throws YouTubeOperationFailed
     * @throws VideoNotFound
     */
    public function handle(DomainEvent $event): void
    {
        assert($event instanceof ShortCreated);

        $this->publishVideo->execute(
            new PublishVideoRequest(videoId: $event->videoId(), platform: 'youtube')
        );

        $this->generateShort->execute(
            new GenerateShortRequest(videoId: $event->parentId())
        );
    }
}
