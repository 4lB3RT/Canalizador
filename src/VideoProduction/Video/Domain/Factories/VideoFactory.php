<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Video\Domain\Factories;

use Helmreel\Shared\Shared\Domain\Services\Clock;
use Helmreel\Shared\Shared\Domain\ValueObjects\Description;
use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\DateTime;
use Helmreel\Shared\Shared\Domain\ValueObjects\Title;
use Helmreel\VideoProduction\Avatar\Domain\ValueObjects\AvatarId;
use Helmreel\VideoProduction\Script\Domain\Entities\Script;
use Helmreel\VideoProduction\Video\Domain\Entities\Video;
use Helmreel\VideoProduction\Video\Domain\ValueObjects\VideoCategory;
use Helmreel\VideoProduction\Video\Domain\ValueObjects\VideoId;
use Helmreel\YouTube\Channel\Domain\ValueObjects\ChannelId;

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
