<?php

declare(strict_types = 1);

namespace Helmreel\YouTube\Video\Application\Handlers;

use Helmreel\Shared\Shared\Domain\Events\DomainEvent;
use Helmreel\Shared\Shared\Domain\Events\DomainEventHandler;
use Helmreel\YouTube\Channel\Domain\Exceptions\ChannelNotFound;
use Helmreel\YouTube\Channel\Domain\Repositories\ChannelRepository;
use Helmreel\YouTube\Video\Application\UseCases\GenerateShort\GenerateShort;
use Helmreel\YouTube\Video\Application\UseCases\GenerateShort\GenerateShortRequest;
use Helmreel\YouTube\Video\Domain\Events\ShortCreated;
use Helmreel\YouTube\Video\Domain\Exceptions\VideoFragmentationFailed;
use Helmreel\YouTube\Video\Domain\Exceptions\VideoNotFound;
use Helmreel\YouTube\Video\Domain\Exceptions\YouTubeOperationFailed;
use Helmreel\YouTube\Video\Domain\Repositories\VideoRepository;
use Helmreel\YouTube\Video\Domain\ValueObjects\Id;
use Helmreel\YouTube\Video\Infrastructure\Jobs\PublishShortJob;

final readonly class OnYouTubeShortCreatedHandler implements DomainEventHandler
{
    public function __construct(
        private GenerateShort     $generateShort,
        private VideoRepository   $videoRepository,
        private ChannelRepository $channelRepository,
    ) {
    }

    /**
     * @throws VideoFragmentationFailed
     * @throws YouTubeOperationFailed
     * @throws VideoNotFound
     * @throws ChannelNotFound
     */
    public function handle(DomainEvent $event): void
    {
        assert($event instanceof ShortCreated);

        $video = $this->videoRepository->findById(new Id($event->videoId()));
        $channel = $this->channelRepository->findById($video->channelId());

        if ($channel->autoPublish()) {
            PublishShortJob::dispatch(
                videoId:  $event->videoId(),
                platform: 'youtube',
            );
        }

        $this->generateShort->execute(
            new GenerateShortRequest(videoId: $event->parentId())
        );
    }
}
