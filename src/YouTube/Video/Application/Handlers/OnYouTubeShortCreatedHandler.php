<?php

declare(strict_types = 1);

namespace Canalizador\YouTube\Video\Application\Handlers;

use Canalizador\Shared\Shared\Domain\Events\DomainEvent;
use Canalizador\Shared\Shared\Domain\Events\DomainEventHandler;
use Canalizador\YouTube\Channel\Domain\Exceptions\ChannelNotFound;
use Canalizador\YouTube\Channel\Domain\Repositories\ChannelRepository;
use Canalizador\YouTube\Video\Application\UseCases\GenerateShort\GenerateShort;
use Canalizador\YouTube\Video\Application\UseCases\GenerateShort\GenerateShortRequest;
use Canalizador\YouTube\Video\Domain\Events\ShortCreated;
use Canalizador\YouTube\Video\Domain\Exceptions\VideoFragmentationFailed;
use Canalizador\YouTube\Video\Domain\Exceptions\VideoNotFound;
use Canalizador\YouTube\Video\Domain\Exceptions\YouTubeOperationFailed;
use Canalizador\YouTube\Video\Domain\Repositories\VideoRepository;
use Canalizador\YouTube\Video\Domain\ValueObjects\Id;
use Canalizador\YouTube\Video\Infrastructure\Jobs\PublishShortJob;

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
