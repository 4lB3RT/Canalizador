<?php

declare(strict_types = 1);

namespace Canalizador\Video\Infrastructure\Repositories\Redis;

use Canalizador\Metric\Domain\Entities\MetricCollection;
use Canalizador\Video\Domain\Entities\Video;
use Canalizador\Video\Domain\Repositories\VideoRepository;
use Canalizador\Video\Domain\ValueObjects\VideoId;
use Canalizador\Video\Infrastructure\DataTransformers\VideoDataTransformer;
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
            $video->toArray(),
        ]);

        $this->redis->set($key, $data);
    }

    public function findById(VideoId $videoId): ?Video
    {
        $key  = 'video:' . $videoId->value();
        $data = $this->redis->get($key);

        if (!$data) {
            return null;
        }

        $videoArray = json_decode($data, true)[0];

        return VideoDataTransformer::fromArray($videoArray);
    }

    public function getMetricsById(VideoId $videoId): ?MetricCollection
    {
        // TODO: Implement getMetricsById() method.
    }
}
