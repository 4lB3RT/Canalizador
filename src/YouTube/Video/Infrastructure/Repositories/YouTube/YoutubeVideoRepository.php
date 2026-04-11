<?php

declare(strict_types = 1);

namespace Canalizador\YouTube\Video\Infrastructure\Repositories\YouTube;

use Canalizador\YouTube\Channel\Domain\ValueObjects\ChannelId;
use Canalizador\YouTube\Shared\Infrastructure\ClientAPI\YoutubeDataApiClient;
use Canalizador\YouTube\Video\Domain\Entities\Video;
use Canalizador\YouTube\Video\Domain\Exceptions\VideoNotFound;
use Canalizador\YouTube\Video\Domain\Repositories\VideoRepository;
use Canalizador\YouTube\Video\Domain\ValueObjects\Category;
use Canalizador\YouTube\Video\Domain\ValueObjects\Id;
use Canalizador\YouTube\Video\Domain\ValueObjects\PlatformId;
use Canalizador\YouTube\Video\Domain\ValueObjects\YouTubeStatus;
use Canalizador\YouTube\Video\Infrastructure\DataTransformers\VideoDataTransformer;
use DateInterval;

final readonly class YoutubeVideoRepository implements VideoRepository
{
    public function __construct(
        private YoutubeDataApiClient $youtubeClient,
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

    public function save(Video $video): void
    {
        throw new \LogicException('YoutubeVideoRepository is read-only');
    }
}
