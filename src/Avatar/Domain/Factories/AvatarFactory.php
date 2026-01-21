<?php

declare(strict_types=1);

namespace Canalizador\Avatar\Domain\Factories;

use Canalizador\Avatar\Domain\Entities\Avatar;
use Canalizador\Avatar\Domain\ValueObjects\AvatarDescription;
use Canalizador\Avatar\Domain\ValueObjects\AvatarId;
use Canalizador\Avatar\Domain\ValueObjects\AvatarName;
use Canalizador\Avatar\Domain\ValueObjects\Biography;
use Canalizador\Avatar\Domain\ValueObjects\PresentationStyle;
use Canalizador\Image\Domain\Entities\ImageCollection;
use Canalizador\Shared\Domain\Services\Clock;
use Canalizador\Shared\Domain\ValueObjects\DateTime;
use Canalizador\Shared\Domain\ValueObjects\IntegerId;
use Canalizador\Shared\Domain\ValueObjects\LocalPath;

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
        AvatarDescription $description,
        ?DateTime $createdAt = null,
        ?ImageCollection $images = null
    ): Avatar {
        return new Avatar(
            id: $id,
            userId: $userId,
            name: $name,
            profileImagePath: $profileImagePath,
            createdAt: $createdAt ?? $this->clock->now(),
            biography: $biography,
            presentationStyle: $presentationStyle,
            description: $description,
            images: $images ?? ImageCollection::empty(),
            clock: $this->clock,
        );
    }
}

