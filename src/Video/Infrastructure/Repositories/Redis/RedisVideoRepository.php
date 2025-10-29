<?php

declare(strict_types=1);

namespace Canalizador\Video\Infrastructure\Repositories\Redis;

use Canalizador\Metric\Domain\Entities\MetricCollection;
use Canalizador\Shared\Domain\ValueObjects\DateTime;
use Canalizador\Shared\Domain\ValueObjects\LocalPath;
use Canalizador\Shared\Domain\ValueObjects\Url;
use Canalizador\Transcription\Domain\Entities\Transcription;
use Canalizador\Video\Domain\Entities\Video;
use Canalizador\Video\Domain\Repositories\VideoRepository;
use Canalizador\Video\Domain\ValueObjects\Category;
use Canalizador\Video\Domain\ValueObjects\Title;
use Canalizador\Video\Domain\ValueObjects\VideoId;
use Illuminate\Redis\Connections\Connection;

final readonly class RedisVideoRepository implements VideoRepository
{
    public function __construct(private Connection $redis)
    {
    }

    public function save(Video $video): void
    {
        $key = 'video:' . $video->id()->value();

        $data = json_encode([
            $video->toArray()
        ]);

        $this->redis->set($key, $data);
    }

    public function findById(VideoId $videoId): ?Video
    {
        $key = 'video:' . $videoId->value();
        $data = $this->redis->get($key);

        if (!$data) {
            return null;
        }

        $videoArray = json_decode($data, true)[0];
        $publishedAt = new DateTime(\DateTimeImmutable::createFromFormat('Y-m-d H:i:s.u', $videoArray['published_at']['date']));

        return new Video(
            id: VideoId::fromString($videoArray['id']),
            title: Title::fromString($videoArray['title']),
            publishedAt: $publishedAt,
            metrics: MetricCollection::fromArray($videoArray['metrics']),
            category: Category::fromString($videoArray['category']),
            url: $videoArray['url'] !== null ? Url::fromString($videoArray['url']) : null,
            videoLocalPath: LocalPath::fromString($videoArray['video_local_path']),
            audioLocalPath: $videoArray['audio_local_path'] !== null ? LocalPath::fromString($videoArray['audio_local_path']) : null,
            transcription: $videoArray['transcription'] !== null ? $videoArray['transcription'] : null
        );
    }

    public function getMetricsById(VideoId $videoId): ?MetricCollection
    {
        // TODO: Implement getMetricsById() method.
    }
}
