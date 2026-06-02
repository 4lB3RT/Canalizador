<?php

declare(strict_types=1);

namespace Helmreel\YouTube\Video\Application\UseCases\SyncVideo;

use Helmreel\Shared\Shared\Domain\Events\EventBus;
use Helmreel\YouTube\Channel\Domain\Exceptions\ChannelNotFound;
use Helmreel\YouTube\Channel\Domain\Repositories\ChannelRepository;
use Helmreel\YouTube\Video\Domain\Exceptions\VideoNotFound;
use Helmreel\YouTube\Video\Domain\Repositories\VideoRepository;
use Helmreel\YouTube\Video\Infrastructure\Builders\YouTubeVideoBuilder;
use Helmreel\YouTube\Video\Infrastructure\Repositories\YouTube\YoutubeVideoRepository;
use Throwable;

final readonly class SyncVideo
{
    public function __construct(
        private ChannelRepository       $channelRepository,
        private VideoRepository         $internalVideoRepository,
        private YoutubeVideoRepository  $externalVideoRepository,
        private YouTubeVideoBuilder     $videoBuilder,
        private EventBus                $eventBus,
    ) {
    }

    /**
     * @throws VideoNotFound
     * @throws ChannelNotFound
     * @throws Throwable
     */
    public function execute(SyncVideoRequest $request): void
    {
        $externalVideo = $this->externalVideoRepository->findByPlatformId($request->platformId);

        $channel = $this->channelRepository->findById($externalVideo->channelId());

        if (!$channel->userId()->equals($request->userId)) {
            throw VideoNotFound::withId($request->platformId->value());
        }

        $video = $this->videoBuilder
            ->fromPlatformId($request->platformId)
            ->withDownload()
            ->withAudio()
            ->withTranscription()
            ->build();

        $this->internalVideoRepository->save($video);

        $this->eventBus->publish(...$video->releaseEvents());
    }
}
