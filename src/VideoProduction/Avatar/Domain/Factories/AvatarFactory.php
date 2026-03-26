<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Avatar\Domain\Factories;

use Canalizador\VideoProduction\Avatar\Domain\Entities\Avatar;
use Canalizador\VideoProduction\Avatar\Domain\ValueObjects\AvatarDescription;
use Canalizador\VideoProduction\Avatar\Domain\ValueObjects\AvatarId;
use Canalizador\VideoProduction\Avatar\Domain\ValueObjects\AvatarName;
use Canalizador\VideoProduction\Avatar\Domain\ValueObjects\Biography;
use Canalizador\VideoProduction\Avatar\Domain\ValueObjects\Category;
use Canalizador\VideoProduction\Avatar\Domain\ValueObjects\PresentationStyle;
use Canalizador\VideoProduction\Image\Domain\Entities\ImageCollection;
use Canalizador\Shared\Domain\Services\Clock;
use Canalizador\Shared\Domain\ValueObjects\DateTime;
use Canalizador\Shared\Domain\ValueObjects\IntegerId;
use Canalizador\Shared\Domain\ValueObjects\LocalPath;
use Canalizador\VideoProduction\Voice\Domain\ValueObjects\VoiceId;

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

