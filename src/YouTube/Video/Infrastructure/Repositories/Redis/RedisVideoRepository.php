<?php

declare(strict_types=1);

namespace Helmreel\YouTube\Video\Infrastructure\Repositories\Redis;

use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;
use Helmreel\Shared\Shared\Domain\ValueObjects\Pagination;
use Helmreel\Shared\Shared\Domain\ValueObjects\Search;
use Helmreel\Shared\Shared\Domain\ValueObjects\Total;
use Helmreel\YouTube\Channel\Domain\ValueObjects\ChannelId;
use Helmreel\YouTube\Video\Domain\Entities\Video;
use Helmreel\YouTube\Video\Domain\Entities\VideoCollection;
use Helmreel\YouTube\Video\Domain\Exceptions\VideoNotFound;
use Helmreel\YouTube\Video\Domain\Repositories\VideoRepository;
use Helmreel\YouTube\Video\Domain\ValueObjects\Category;
use Helmreel\YouTube\Video\Domain\ValueObjects\Id;
use Helmreel\YouTube\Video\Domain\ValueObjects\PlatformId;
use Helmreel\YouTube\Video\Infrastructure\DataTransformers\VideoDataTransformer;
use Illuminate\Redis\Connections\Connection;
use RuntimeException;

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

    public function findScheduledShortsByChannelId(ChannelId $channelId): VideoCollection
    {
        return new VideoCollection([]);
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

    public function findByPlatformId(PlatformId $platformId): Video
    {
        throw new RuntimeException('findByPlatformId is not supported by RedisVideoRepository');
    }

    public function findLastByChannelId(ChannelId $channelId, ?Category $category = null): ?PlatformId
    {
        return null;
    }

    public function findByChannelId(
        ChannelId $channelId,
        ?Category $category = null,
        ?Pagination $pagination = null,
    ): VideoCollection {
        return new VideoCollection([]);
    }

    public function countByChannelId(ChannelId $channelId, ?Category $category = null): Total
    {
        return Total::fromInt(0);
    }

    public function findByUserId(
        IntegerId $userId,
        ?Category $category = null,
        ?ChannelId $channelId = null,
        ?Search $search = null,
        ?Pagination $pagination = null,
    ): VideoCollection {
        return new VideoCollection([]);
    }

    public function countByUserId(
        IntegerId $userId,
        ?Category $category = null,
        ?ChannelId $channelId = null,
        ?Search $search = null,
    ): Total {
        return Total::fromInt(0);
    }
}
