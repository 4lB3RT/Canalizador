<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Video\Domain\Factories;

use Helmreel\Shared\Shared\Domain\Services\Clock;
use Helmreel\Shared\Shared\Domain\ValueObjects\Description;
use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\DateTime;
use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;
use Helmreel\Shared\Shared\Domain\ValueObjects\Language;
use Helmreel\Shared\Shared\Domain\ValueObjects\Title;
use Helmreel\VideoProduction\Avatar\Domain\ValueObjects\AvatarId;
use Helmreel\VideoProduction\Script\Domain\Entities\Script;
use Helmreel\VideoProduction\Video\Domain\Entities\Video;
use Helmreel\VideoProduction\Video\Domain\ValueObjects\Resolution;
use Helmreel\VideoProduction\Video\Domain\ValueObjects\TotalClips;
use Helmreel\VideoProduction\Video\Domain\ValueObjects\VideoCategory;
use Helmreel\VideoProduction\Video\Domain\ValueObjects\VideoId;

final readonly class VideoFactory
{
    public function __construct(
        private Clock $clock
    ) {
    }

    public function create(
        VideoId $id,
        IntegerId $userId,
        Script $script,
        Title $title,
        Description $description,
        VideoCategory $category,
        Resolution $resolution = Resolution::HD,
        TotalClips $totalClips = new TotalClips(5),
        Language $language = Language::SPANISH,
        ?AvatarId $avatarId = null,
        ?DateTime $createdAt = null,
    ): Video {
        return new Video(
            id: $id,
            userId: $userId,
            script: $script,
            title: $title,
            description: $description,
            category: $category,
            createdAt: $createdAt ?? $this->clock->now(),
            resolution: $resolution,
            totalClips: $totalClips,
            language: $language,
            avatarId: $avatarId,
        );
    }
}
