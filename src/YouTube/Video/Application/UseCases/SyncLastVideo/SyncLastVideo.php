<?php

declare(strict_types = 1);

namespace Helmreel\YouTube\Video\Application\UseCases\SyncLastVideo;

use Helmreel\Shared\Shared\Domain\Events\EventBus;
use Helmreel\YouTube\Channel\Domain\Exceptions\ChannelNotFound;
use Helmreel\YouTube\Channel\Domain\Repositories\ChannelRepository;
use Helmreel\YouTube\Channel\Domain\ValueObjects\ChannelId;
use Helmreel\YouTube\Video\Domain\Exceptions\VideoNotFound;
use Helmreel\YouTube\Video\Domain\Repositories\VideoRepository;
use Helmreel\YouTube\Video\Domain\ValueObjects\Category;
use Helmreel\YouTube\Video\Infrastructure\Builders\YouTubeVideoBuilder;
use DateMalformedIntervalStringException;
use DateMalformedStringException;
use Throwable;

final readonly class SyncLastVideo
{
    public function __construct(
        private ChannelRepository   $channelRepository,
        private VideoRepository     $internalVideoRepository,
        private VideoRepository     $externalVideoRepository,
        private YouTubeVideoBuilder $videoBuilder,
        private EventBus            $eventBus,
    ) {
    }

    /**
     * @throws ChannelNotFound
     * @throws DateMalformedIntervalStringException
     * @throws DateMalformedStringException
     * @throws Throwable
     * @throws VideoNotFound
     */
    public function execute(SyncLastVideoRequest $request): void
    {
        $channelId = ChannelId::fromString($request->channelId);

        $this->channelRepository->findById($channelId);
        $platformId = $this->externalVideoRepository->findLastByChannelId($channelId, Category::VIDEO);

        if ($platformId === null) {
            return;
        }

        $video = $this->videoBuilder
            ->fromPlatformId($platformId)
            ->withDownload()
            ->withAudio()
            ->withTranscription()
            ->build();

        $this->internalVideoRepository->save($video);

        $this->eventBus->publish(...$video->releaseEvents());
    }
}
