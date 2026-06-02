<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Avatar\Domain\Factories;

use Helmreel\Shared\Shared\Domain\Services\Clock;
use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\DateTime;
use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;
use Helmreel\Shared\Shared\Domain\ValueObjects\LocalPath;
use Helmreel\VideoProduction\Avatar\Domain\Entities\Avatar;
use Helmreel\VideoProduction\Avatar\Domain\ValueObjects\AvatarDescription;
use Helmreel\VideoProduction\Avatar\Domain\ValueObjects\AvatarId;
use Helmreel\VideoProduction\Avatar\Domain\ValueObjects\AvatarName;
use Helmreel\VideoProduction\Avatar\Domain\ValueObjects\Biography;
use Helmreel\VideoProduction\Avatar\Domain\ValueObjects\Category;
use Helmreel\VideoProduction\Avatar\Domain\ValueObjects\PresentationStyle;
use Helmreel\VideoProduction\Image\Domain\Entities\ImageCollection;
use Helmreel\VideoProduction\Voice\Domain\ValueObjects\VoiceId;

final readonly class AvatarFactory
{
    public function __construct(
        private Clock $clock
    ) {
    }

    public function create(
        AvatarId $id,
        IntegerId $userId,
        AvatarName $name,
        LocalPath $profileImagePath,
        Biography $biography,
        PresentationStyle $presentationStyle,
        Category $category,
        AvatarDescription $description,
        ?DateTime $createdAt = null,
        ?ImageCollection $images = null,
        ?VoiceId $voiceId = null,
    ): Avatar {
        return new Avatar(
            id: $id,
            userId: $userId,
            voiceId: $voiceId,
            name: $name,
            profileImagePath: $profileImagePath,
            createdAt: $createdAt ?? $this->clock->now(),
            biography: $biography,
            presentationStyle: $presentationStyle,
            category: $category,
            description: $description,
            images: $images ?? ImageCollection::empty(),
            clock: $this->clock,
        );
    }
}

