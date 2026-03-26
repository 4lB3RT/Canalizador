<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Infrastructure\Repositories\Redis;

use Canalizador\YouTube\Video\Domain\Entities\Video;
use Canalizador\YouTube\Video\Domain\Exceptions\VideoNotFound;
use Canalizador\YouTube\Video\Domain\Repositories\VideoRepository;
use Canalizador\YouTube\Video\Domain\ValueObjects\Id;
use Canalizador\YouTube\Video\Infrastructure\DataTransformers\VideoDataTransformer;
use Illuminate\Redis\Connections\Connection;

final readonly class RedisVideoRepository implements VideoRepository
{
    public function __construct(private Connection $redis)
    {
    }

    public function save(Video $video): void
    {
        $key = 'video:' . $video->id()->value();

        $this->redis->set($key, json_encode($video->toArray()));
    }

    /**
     * @throws VideoNotFound
     */
    public function findById(Id $id): Video
    {
        $key  = 'video:' . $id->value();
        $data = $this->redis->get($key);

        if (!$data) {
            throw VideoNotFound::withId($id->value());
        }

        return VideoDataTransformer::fromArray(json_decode($data, true));
    }
}
