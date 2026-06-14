<?php

declare(strict_types=1);

namespace Helmreel\YouTube\Video\Application\UseCases\PublishProductionVideo;

use Helmreel\Shared\Shared\Domain\Services\Clock;
use Helmreel\Shared\Shared\Domain\ValueObjects\Description;
use Helmreel\Shared\Shared\Domain\ValueObjects\Duration;
use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\DateTime;
use Helmreel\Shared\Media\Domain\Repositories\MediaRepository;
use Helmreel\Shared\Media\Domain\ValueObjects\MediaId;
use Helmreel\Shared\Shared\Domain\ValueObjects\LocalPath;
use Helmreel\Shared\Shared\Domain\ValueObjects\Title;
use Helmreel\YouTube\Channel\Domain\ValueObjects\ChannelId;
use Helmreel\YouTube\Video\Domain\Entities\Video;
use Helmreel\YouTube\Video\Domain\Entities\VideoCollection;
use Helmreel\YouTube\Video\Domain\Exceptions\PublishAtRequired;
use Helmreel\YouTube\Video\Domain\Factories\VideoPublisherFactory;
use Helmreel\YouTube\Video\Domain\Repositories\VideoRepository;
use Helmreel\YouTube\Video\Domain\ValueObjects\Category;
use Helmreel\YouTube\Video\Domain\ValueObjects\Id;
use Helmreel\YouTube\Video\Domain\ValueObjects\YouTubeStatus;

final readonly class PublishProductionVideo
{
    private const string PLATFORM_YOUTUBE = 'youtube';
    private const string YOUTUBE_VIDEO_URL_PREFIX = 'https://www.youtube.com/watch?v=';
    private const int DEFAULT_DURATION_SECONDS = 60;

    public function __construct(
        private VideoRepository $internalVideoRepository,
        private VideoPublisherFactory $videoPublisherFactory,
        private MediaRepository $mediaRepository,
        private Clock $clock,
    ) {
    }

    public function execute(PublishProductionVideoRequest $request): PublishProductionVideoResponse
    {
        $status = YouTubeStatus::from($request->privacy);

        $isScheduled = $status === YouTubeStatus::Scheduled;

        if ($isScheduled && $request->publishAt === null) {
            throw PublishAtRequired::forScheduledVideo($request->videoId);
        }

        $publishedAt = $isScheduled
            ? new DateTime(new \DateTimeImmutable($request->publishAt))
            : $this->clock->now();

        $media = $this->mediaRepository->findById(MediaId::fromString($request->mediaId));

        $video = Video::create(
            id: Id::fromString($request->videoId),
            channelId: ChannelId::fromString($request->channelId),
            title: Title::fromString($request->title),
            publishedAt: $publishedAt,
            duration: Duration::fromInt(self::DEFAULT_DURATION_SECONDS),
            category: Category::VIDEO,
            status: $status,
            clock: $this->clock,
            shorts: new VideoCollection([]),
            videoLocalPath: LocalPath::fromString($media->path()->value()),
            description: Description::fromString($request->description),
        );

        $this->internalVideoRepository->save($video);

        $this->videoPublisherFactory->create(self::PLATFORM_YOUTUBE)->publish($video);

        $this->internalVideoRepository->save($video);

        return new PublishProductionVideoResponse(
            platformVideoId: $video->platformId()?->value() ?? '',
            platformUrl: $video->url()?->value()
                ?? self::YOUTUBE_VIDEO_URL_PREFIX . ($video->platformId()?->value() ?? ''),
            privacy: $request->privacy,
        );
    }
}
