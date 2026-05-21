<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Domain\Repositories;

use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;
use Canalizador\Shared\Shared\Domain\ValueObjects\Pagination;
use Canalizador\Shared\Shared\Domain\ValueObjects\Search;
use Canalizador\Shared\Shared\Domain\ValueObjects\Total;
use Canalizador\YouTube\Channel\Domain\ValueObjects\ChannelId;
use Canalizador\YouTube\Video\Domain\Entities\Video;
use Canalizador\YouTube\Video\Domain\Entities\VideoCollection;
use Canalizador\YouTube\Video\Domain\Exceptions\VideoNotFound;
use Canalizador\YouTube\Video\Domain\ValueObjects\Category;
use Canalizador\YouTube\Video\Domain\ValueObjects\Id;
use Canalizador\YouTube\Video\Domain\ValueObjects\PlatformId;

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
