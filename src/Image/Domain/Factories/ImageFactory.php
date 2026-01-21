<?php

declare(strict_types=1);

namespace Canalizador\Image\Domain\Factories;

use Canalizador\Image\Domain\Entities\Image;
use Canalizador\Image\Domain\ValueObjects\ImageId;
use Canalizador\Shared\Domain\Services\Clock;
use Canalizador\Shared\Domain\ValueObjects\DateTime;
use Canalizador\Shared\Domain\ValueObjects\IntegerId;
use Canalizador\Shared\Domain\ValueObjects\LocalPath;

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
