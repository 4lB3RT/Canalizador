<?php

declare(strict_types = 1);

namespace Canalizador\YouTube\Video\Application\UseCases\PublishVideo;

use Canalizador\Shared\Shared\Domain\Services\Clock;
use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\DateTime;
use Canalizador\YouTube\Video\Domain\Entities\Video;
use Canalizador\YouTube\Video\Domain\Exceptions\VideoLocalPathNotSet;
use Canalizador\YouTube\Video\Domain\Exceptions\VideoNotFound;
use Canalizador\YouTube\Video\Domain\Exceptions\YouTubeOperationFailed;
use Canalizador\YouTube\Video\Domain\Factories\VideoPublisherFactory;
use Canalizador\YouTube\Video\Domain\Repositories\VideoRepository;
use Canalizador\YouTube\Video\Domain\ValueObjects\Id;
use DateTimeImmutable;
use DateTimeZone;

final readonly class PublishVideo
{
    private const array LANE_HOURS = [15, 21, 17];

    private const string PUBLISH_TIMEZONE = 'Europe/Madrid';

    private const string YOUTUBE_VIDEO_URL_PREFIX = 'https://www.youtube.com/watch?v=';

    public function __construct(
        private VideoRepository       $videoRepository,
        private VideoPublisherFactory $videoPublisherFactory,
        private Clock                 $clock,
    ) {
    }

    /**
     * @throws YouTubeOperationFailed
     * @throws VideoNotFound
     */
    public function execute(PublishVideoRequest $request): PublishVideoResponse
    {
        $short = $this->videoRepository->findById(new Id($request->videoId));

        if ($short->videoLocalPath() === null) {
            throw VideoLocalPathNotSet::forVideoId($request->videoId);
        }

        $publishAt = $this->publishSlot($short);
        $short->scheduleAt($publishAt);

        $this->videoPublisherFactory->create($request->platform)->publish($short);

        $short->markAsPublic();
        $this->videoRepository->save($short);

        return new PublishVideoResponse(
            platformVideoId: $short->platformId()?->value() ?? '',
            platformUrl:     $short->url()?->value() ?? self::YOUTUBE_VIDEO_URL_PREFIX . ($short->platformId()?->value() ?? ''),
            platform:        $request->platform,
            scheduledAt:     $publishAt->value()->format(DateTimeImmutable::RFC3339),
        );
    }

    private function publishSlot(Video $short): DateTime
    {
        $pendingByVideo = $this->pendingShortsBySourceVideo($short);
        $timezone       = new DateTimeZone(self::PUBLISH_TIMEZONE);
        $now            = $this->clock->now()->value()->setTimezone($timezone);
        $targetShortId  = $short->id()->value();
        $day            = $now->setTime(0, 0, 0);

        for ($i = 0; $i < 365; $i++, $day = $day->modify('+1 day')) {
            $activeVideos = array_keys(array_filter($pendingByVideo));

            if ($activeVideos === []) {
                break;
            }

            foreach (self::LANE_HOURS as $lane => $hour) {
                $sourceVideoId = $activeVideos[$lane] ?? null;

                if ($sourceVideoId === null) {
                    continue;
                }

                $slot = $day->setTime($hour, 0, 0);

                if ($slot <= $now) {
                    continue;
                }

                if (array_shift($pendingByVideo[$sourceVideoId]) === $targetShortId) {
                    return new DateTime($slot);
                }
            }
        }

        throw YouTubeOperationFailed::apiError('No available publishing slot found within the next year.');
    }

    private function pendingShortsBySourceVideo(Video $short): array
    {
        $pending = [];

        foreach ($this->videoRepository->findFutureShorts() as $futureShort) {
            $sourceVideoId = $futureShort->parentId()?->value();

            if ($sourceVideoId === null) {
                continue;
            }

            $pending[$sourceVideoId][] = $futureShort->id()->value();
        }

        $sourceVideoId = $short->parentId()?->value() ?? $short->id()->value();
        $shortId       = $short->id()->value();

        if (!in_array($shortId, $pending[$sourceVideoId] ?? [], true)) {
            $pending[$sourceVideoId][] = $shortId;
        }

        return $pending;
    }
}
