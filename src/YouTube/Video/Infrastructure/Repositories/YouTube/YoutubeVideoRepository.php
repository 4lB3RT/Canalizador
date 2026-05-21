<?php

declare(strict_types = 1);

namespace Canalizador\YouTube\Video\Infrastructure\Repositories\YouTube;

use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;
use Canalizador\Shared\Shared\Domain\ValueObjects\Pagination;
use Canalizador\Shared\Shared\Domain\ValueObjects\Search;
use Canalizador\Shared\Shared\Domain\ValueObjects\Total;
use Canalizador\YouTube\Channel\Domain\Repositories\ChannelRepository;
use Canalizador\YouTube\Channel\Domain\ValueObjects\ChannelId;
use Canalizador\YouTube\Shared\Infrastructure\ClientAPI\YoutubeDataApiClient;
use Canalizador\YouTube\Video\Domain\Entities\Video;
use Canalizador\YouTube\Video\Domain\Entities\VideoCollection;
use Canalizador\YouTube\Video\Domain\Exceptions\VideoNotFound;
use Canalizador\YouTube\Video\Domain\Repositories\VideoRepository;
use Canalizador\YouTube\Video\Domain\ValueObjects\Category;
use Canalizador\YouTube\Video\Domain\ValueObjects\Id;
use Canalizador\YouTube\Video\Domain\ValueObjects\PlatformId;
use Canalizador\YouTube\Video\Domain\ValueObjects\YouTubeStatus;
use Canalizador\YouTube\Video\Infrastructure\DataTransformers\VideoDataTransformer;
use Canalizador\YouTube\Video\Infrastructure\Repositories\Eloquent\EloquentVideoRepository;
use DateInterval;

final readonly class YoutubeVideoRepository implements VideoRepository
{
    public function __construct(
        private YoutubeDataApiClient     $youtubeClient,
        private ChannelRepository        $channelRepository,
        private EloquentVideoRepository  $localVideoRepository,
    ) {
    }

    public function findById(Id $id): Video
    {
        throw VideoNotFound::withId($id->value());
    }

    public function findByPlatformId(PlatformId $platformId): Video
    {
        $data = $this->youtubeClient->getVideoById($platformId->value());

        if (!$data) {
            throw VideoNotFound::withId($platformId->value());
        }

        $durationMinutes = 0;
        if (isset($data['contentDetails']['duration'])) {
            $interval        = new DateInterval($data['contentDetails']['duration']);
            $totalSeconds    = $interval->h * 3600 + $interval->i * 60 + $interval->s;
            $durationMinutes = (int) ceil($totalSeconds / 60);
        }

        $publishedAt = (new \DateTimeImmutable($data['snippet']['publishedAt']))->format('Y-m-d H:i:s');

        return VideoDataTransformer::fromArray([
            'id'               => Id::generate()->value(),
            'channel_id'       => $data['snippet']['channelId'],
            'title'            => $data['snippet']['title'],
            'published_at'     => $publishedAt,
            'duration'         => $durationMinutes,
            'metrics'          => [],
            'category'         => Category::VIDEO->value,
            'status'           => YouTubeStatus::Public->value,
            'platform_id'      => $platformId->value(),
            'url'              => 'https://www.youtube.com/watch?v=' . $platformId->value(),
            'video_local_path' => null,
            'audio_local_path' => null,
            'transcription'    => null,
            'description'      => null,
            'parent_id'        => null,
        ]);
    }

    public function findLastByChannelId(ChannelId $channelId, ?Category $category = null): ?PlatformId
    {
        $youtubeId = $this->youtubeClient->getLastVideoIdByChannelId(
            $channelId->value(),
            $category?->value
        );

        if ($youtubeId === null) {
            return null;
        }

        return PlatformId::fromString($youtubeId);
    }

    public function findScheduledShortsByChannelId(ChannelId $channelId): VideoCollection
    {
        $channel = $this->channelRepository->findById($channelId);

        $scheduled = $this->youtubeClient->getScheduledShortsByChannelId(
            $channelId->value(),
            $channel->userId()->value(),
        );

        $videos = array_map(
            function (array $entry) use ($channelId): Video {
                try {
                    $parentId = $this->localVideoRepository
                        ->findByPlatformId(PlatformId::fromString($entry['platformId']))
                        ->parentId()
                        ?->value();
                } catch (VideoNotFound) {
                    $parentId = null;
                }

                return VideoDataTransformer::fromArray([
                    'id'               => Id::generate()->value(),
                    'platform_id'      => $entry['platformId'],
                    'parent_id'        => $parentId,
                    'channel_id'       => $channelId->value(),
                    'title'            => '',
                    'published_at'     => $entry['publishAt']->format('Y-m-d H:i:s'),
                    'metrics'          => [],
                    'category'         => Category::SHORT->value,
                    'status'           => YouTubeStatus::Scheduled->value,
                    'url'              => 'https://www.youtube.com/watch?v=' . $entry['platformId'],
                    'video_local_path' => null,
                    'audio_local_path' => null,
                    'transcription'    => null,
                    'duration'         => 1,
                    'description'      => null,
                ]);
            },
            $scheduled
        );

        return new VideoCollection($videos);
    }

    public function save(Video $video): void
    {
        throw new \LogicException('YoutubeVideoRepository is read-only');
    }

    public function findByChannelId(
        ChannelId $channelId,
        ?Category $category = null,
        ?Pagination $pagination = null,
    ): VideoCollection {
        throw new \LogicException('findByChannelId is not supported by YoutubeVideoRepository');
    }

    public function countByChannelId(ChannelId $channelId, ?Category $category = null): Total
    {
        throw new \LogicException('countByChannelId is not supported by YoutubeVideoRepository');
    }

    public function findByUserId(
        IntegerId $userId,
        ?Category $category = null,
        ?ChannelId $channelId = null,
        ?Search $search = null,
        ?Pagination $pagination = null,
    ): VideoCollection {
        throw new \LogicException('findByUserId is not supported by YoutubeVideoRepository');
    }

    public function countByUserId(
        IntegerId $userId,
        ?Category $category = null,
        ?ChannelId $channelId = null,
        ?Search $search = null,
    ): Total {
        throw new \LogicException('countByUserId is not supported by YoutubeVideoRepository');
    }
}
