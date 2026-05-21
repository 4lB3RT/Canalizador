<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Application\UseCases\SyncVideo;

use Canalizador\Shared\Shared\Domain\Events\EventBus;
use Canalizador\YouTube\Channel\Domain\Exceptions\ChannelNotFound;
use Canalizador\YouTube\Channel\Domain\Repositories\ChannelRepository;
use Canalizador\YouTube\Video\Domain\Exceptions\VideoNotFound;
use Canalizador\YouTube\Video\Domain\Repositories\VideoRepository;
use Canalizador\YouTube\Video\Infrastructure\Builders\YouTubeVideoBuilder;
use Canalizador\YouTube\Video\Infrastructure\Repositories\YouTube\YoutubeVideoRepository;
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
