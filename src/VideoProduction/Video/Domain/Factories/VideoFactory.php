<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Video\Domain\Factories;

use Canalizador\VideoProduction\Avatar\Domain\ValueObjects\AvatarId;
use Canalizador\YouTube\Channel\Domain\ValueObjects\ChannelId;
use Canalizador\VideoProduction\Script\Domain\Entities\Script;
use Canalizador\VideoProduction\Shared\Domain\Services\Clock;
use Canalizador\VideoProduction\Shared\Domain\ValueObjects\DateTime;
use Canalizador\VideoProduction\Video\Domain\Entities\Video;
use Canalizador\VideoProduction\Video\Domain\ValueObjects\Description;
use Canalizador\VideoProduction\Video\Domain\ValueObjects\GenerationId;
use Canalizador\VideoProduction\Video\Domain\ValueObjects\Title;
use Canalizador\VideoProduction\Video\Domain\ValueObjects\VideoCategory;
use Canalizador\VideoProduction\Video\Domain\ValueObjects\VideoId;

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
