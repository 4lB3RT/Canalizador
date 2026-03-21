<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Avatar\Infrastructure\Repositories\OpenAI;

use Canalizador\VideoProduction\Avatar\Domain\ValueObjects\AvatarDescription;
use Canalizador\VideoProduction\Image\Domain\Entities\ImageCollection;

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
