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
        private VideoRepository       $internalVideoRepository,
        private VideoRepository       $externalVideoRepository,
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
        $short = $this->internalVideoRepository->findById(new Id($request->videoId));

        if ($short->videoLocalPath() === null) {
            throw VideoLocalPathNotSet::forVideoId($request->videoId);
        }

        $publishAt = $this->publishSlot($short);
        $short->scheduleAt($publishAt);

        $this->videoPublisherFactory->create($request->platform)->publish($short);

        $short->markAsPublic();
        $this->internalVideoRepository->save($short);

        return new PublishVideoResponse(
            platformVideoId: $short->platformId()?->value() ?? '',
            platformUrl:     $short->url()?->value() ?? self::YOUTUBE_VIDEO_URL_PREFIX . ($short->platformId()?->value() ?? ''),
            platform:        $request->platform,
            scheduledAt:     $publishAt->value()->format(DateTimeImmutable::RFC3339),
        );
    }

    private function publishSlot(Video $short): DateTime
    {
        $occupiedSlots = $this->occupiedSlots($short);
        $timezone      = new DateTimeZone(self::PUBLISH_TIMEZONE);
        $now           = $this->clock->now()->value()->setTimezone($timezone);
        $day           = $now->setTime(0, 0, 0);

        for ($i = 0; $i < 365; $i++, $day = $day->modify('+1 day')) {
            foreach (self::LANE_HOURS as $hour) {
                $slot = $day->setTime($hour, 0, 0);

                if ($slot <= $now) {
                    continue;
                }

                if (!isset($occupiedSlots[$this->slotKey($slot)])) {
                    return new DateTime($slot);
                }
            }
        }

        throw YouTubeOperationFailed::apiError('No available publishing slot found within the next year.');
    }

    /**
     * @return array<string, true>
     */
    private function occupiedSlots(Video $short): array
    {
        $scheduled = $this->externalVideoRepository->findScheduledShortsByChannelId($short->channelId());
        $timezone  = new DateTimeZone(self::PUBLISH_TIMEZONE);
        $occupied  = [];

        foreach ($scheduled->items() as $scheduledShort) {
            $slot = $scheduledShort->publishedAt()->value()->setTimezone($timezone);
            $occupied[$this->slotKey($slot)] = true;
        }

        return $occupied;
    }

    private function slotKey(DateTimeImmutable $slot): string
    {
        return $slot->format('Y-m-d H');
    }
}
