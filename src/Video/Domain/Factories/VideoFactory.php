<?php

declare(strict_types=1);

namespace Canalizador\Video\Domain\Factories;

use Canalizador\Avatar\Domain\ValueObjects\AvatarId;
use Canalizador\Channel\Domain\ValueObjects\ChannelId;
use Canalizador\Script\Domain\Entities\Script;
use Canalizador\Shared\Domain\Services\Clock;
use Canalizador\Shared\Domain\ValueObjects\DateTime;
use Canalizador\Video\Domain\Entities\Video;
use Canalizador\Video\Domain\ValueObjects\Description;
use Canalizador\Video\Domain\ValueObjects\GenerationId;
use Canalizador\Video\Domain\ValueObjects\Title;
use Canalizador\Video\Domain\ValueObjects\VideoCategory;
use Canalizador\Video\Domain\ValueObjects\VideoId;

final readonly class VideoFactory
{
    public function __construct(
        private Clock $clock
    ) {
    }

    public function create(
        VideoId $id,
        Script $script,
        ChannelId $channelId,
        Title $title,
        Description $description,
        VideoCategory $category,
        ?AvatarId $avatarId = null,
        ?DateTime $createdAt = null,
    ): Video {
        return new Video(
            id: $id,
            script: $script,
            channelId: $channelId,
            title: $title,
            description: $description,
            category: $category,
            createdAt: $createdAt ?? $this->clock->now(),
            avatarId: $avatarId,
        );
    }
}
