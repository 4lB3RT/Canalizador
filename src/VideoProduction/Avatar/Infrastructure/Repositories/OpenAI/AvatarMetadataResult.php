<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Avatar\Infrastructure\Repositories\OpenAI;

use Helmreel\VideoProduction\Avatar\Domain\ValueObjects\AvatarDescription;
use Helmreel\VideoProduction\Image\Domain\Entities\ImageCollection;

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
