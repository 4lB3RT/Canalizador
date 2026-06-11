<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Image\Domain\Factories;

use Helmreel\Shared\Shared\Domain\Services\Clock;
use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\DateTime;
use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;
use Helmreel\Shared\Shared\Domain\ValueObjects\LocalPath;
use Helmreel\VideoProduction\Image\Domain\Entities\Image;
use Helmreel\VideoProduction\Image\Domain\ValueObjects\ImageId;
use Helmreel\VideoProduction\Image\Domain\ValueObjects\ImageType;

final readonly class ImageFactory
{
    public function __construct(
        private Clock $clock
    ) {
    }

    public function create(
        ImageId $id,
        IntegerId $userId,
        LocalPath $path,
        ?DateTime $createdAt = null,
        ImageType $type = ImageType::GENERATED,
    ): Image {
        return new Image(
            id: $id,
            userId: $userId,
            path: $path,
            createdAt: $createdAt ?? $this->clock->now(),
            type: $type,
        );
    }
}
