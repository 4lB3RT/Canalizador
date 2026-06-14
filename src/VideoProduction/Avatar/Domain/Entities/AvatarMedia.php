<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Avatar\Domain\Entities;

use Helmreel\VideoProduction\Avatar\Domain\ValueObjects\AvatarMediaType;
use Helmreel\Shared\Media\Domain\Entities\Media;

final readonly class AvatarMedia
{
    public function __construct(
        private Media $media,
        private AvatarMediaType $type,
    ) {
    }

    public function media(): Media
    {
        return $this->media;
    }

    public function type(): AvatarMediaType
    {
        return $this->type;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->media->id()->value(),
            'url' => '/video-production/media/' . $this->media->id()->value(),
            'type' => $this->type->value,
        ];
    }
}
