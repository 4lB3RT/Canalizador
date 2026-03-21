<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Image\Domain\Factories;

use Canalizador\VideoProduction\Image\Domain\Entities\Image;
use Canalizador\VideoProduction\Image\Domain\ValueObjects\ImageId;
use Canalizador\VideoProduction\Shared\Domain\Services\Clock;
use Canalizador\VideoProduction\Shared\Domain\ValueObjects\DateTime;
use Canalizador\VideoProduction\Shared\Domain\ValueObjects\IntegerId;
use Canalizador\VideoProduction\Shared\Domain\ValueObjects\LocalPath;

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
        ?DateTime $createdAt = null
    ): Image {
        return new Image(
            id: $id,
            userId: $userId,
            path: $path,
            createdAt: $createdAt ?? $this->clock->now(),
        );
    }
}
