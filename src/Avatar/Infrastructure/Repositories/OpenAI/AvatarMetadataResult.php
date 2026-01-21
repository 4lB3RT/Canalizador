<?php

declare(strict_types=1);

namespace Canalizador\Avatar\Infrastructure\Repositories\OpenAI;

use Canalizador\Avatar\Domain\ValueObjects\AvatarDescription;
use Canalizador\Image\Domain\Entities\ImageCollection;

final readonly class AvatarMetadataResult
{
    public function __construct(
        private AvatarDescription $description,
        private ImageCollection $images
    ) {
    }

    public function description(): AvatarDescription
    {
        return $this->description;
    }

    public function images(): ImageCollection
    {
        return $this->images;
    }
}
