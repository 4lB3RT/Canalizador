<?php

declare(strict_types = 1);

namespace Canalizador\Video\Domain\Factories;

use Canalizador\Script\Domain\Entities\Script;
use Canalizador\Shared\Domain\Services\Clock;
use Canalizador\Shared\Domain\ValueObjects\DateTime;
use Canalizador\Video\Domain\Entities\Video;
use Canalizador\Video\Domain\ValueObjects\GenerationId;
use Canalizador\Video\Domain\ValueObjects\Title;
use Canalizador\Video\Domain\ValueObjects\VideoId;

final readonly class VideoFactory
{
    public function __construct(
        private Clock $clock
    ) {
    }

    public function create(
        VideoId $id,
        Script $script,
        Title $title,
        GenerationId $generationId,
        ?DateTime $createdAt = null
    ): Video {
        return new Video(
            id: $id,
            script: $script,
            title: $title,
            createdAt: $createdAt ?? $this->clock->now(),
            generationId: $generationId,
        );
    }

    public function createFromStrings(
        string $videoId,
        Script $script,
        ?string $title,
        string $generationId,
        ?DateTime $createdAt = null
    ): Video {
        return $this->create(
            id: VideoId::fromString($videoId),
            script: $script,
            title: Title::fromString($title ?? 'Generated Video'),
            generationId: GenerationId::fromString($generationId),
            createdAt: $createdAt,
        );
    }
}
