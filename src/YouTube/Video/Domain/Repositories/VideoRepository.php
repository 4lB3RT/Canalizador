<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Domain\Repositories;

use Canalizador\YouTube\Channel\Domain\ValueObjects\ChannelId;
use Canalizador\YouTube\Video\Domain\Entities\Video;
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

    public function save(Video $video): void;
}
