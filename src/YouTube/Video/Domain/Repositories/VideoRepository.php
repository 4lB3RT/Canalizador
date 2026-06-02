<?php

declare(strict_types=1);

namespace Helmreel\YouTube\Video\Domain\Repositories;

use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;
use Helmreel\Shared\Shared\Domain\ValueObjects\Pagination;
use Helmreel\Shared\Shared\Domain\ValueObjects\Search;
use Helmreel\Shared\Shared\Domain\ValueObjects\Total;
use Helmreel\YouTube\Channel\Domain\ValueObjects\ChannelId;
use Helmreel\YouTube\Video\Domain\Entities\Video;
use Helmreel\YouTube\Video\Domain\Entities\VideoCollection;
use Helmreel\YouTube\Video\Domain\Exceptions\VideoNotFound;
use Helmreel\YouTube\Video\Domain\ValueObjects\Category;
use Helmreel\YouTube\Video\Domain\ValueObjects\Id;
use Helmreel\YouTube\Video\Domain\ValueObjects\PlatformId;

interface VideoRepository
{
    /**
     * @throws VideoNotFound
     */
    public function findById(Id $id): Video;

    /**
     * @throws VideoNotFound
     */
    public function findByPlatformId(PlatformId $platformId): Video;

    public function findLastByChannelId(ChannelId $channelId, ?Category $category = null): ?PlatformId;

    public function findScheduledShortsByChannelId(ChannelId $channelId): VideoCollection;

    public function findByChannelId(
        ChannelId $channelId,
        ?Category $category = null,
        ?Pagination $pagination = null,
    ): VideoCollection;

    public function countByChannelId(ChannelId $channelId, ?Category $category = null): Total;

    public function findByUserId(
        IntegerId $userId,
        ?Category $category = null,
        ?ChannelId $channelId = null,
        ?Search $search = null,
        ?Pagination $pagination = null,
    ): VideoCollection;

    public function countByUserId(
        IntegerId $userId,
        ?Category $category = null,
        ?ChannelId $channelId = null,
        ?Search $search = null,
    ): Total;

    public function save(Video $video): void;
}
