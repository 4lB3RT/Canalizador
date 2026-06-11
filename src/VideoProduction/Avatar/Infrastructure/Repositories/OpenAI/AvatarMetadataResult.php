<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Avatar\Infrastructure\Repositories\OpenAI;

use Helmreel\VideoProduction\Avatar\Domain\Entities\AvatarMedia;
use Helmreel\VideoProduction\Avatar\Domain\ValueObjects\AvatarDescription;

final readonly class AvatarMetadataResult
{
    /**
     * @param AvatarMedia[] $media
     */
    public function __construct(
        private AvatarDescription $description,
        private array $media,
    ) {
    }

    public function description(): AvatarDescription
    {
        return $this->description;
    }

    /**
     * @return AvatarMedia[]
     */
    public function media(): array
    {
        return $this->media;
    }
}
