<?php

declare(strict_types = 1);

namespace Canalizador\Video\Domain\Factories;

use Canalizador\Script\Domain\Entities\Script;
use Canalizador\Shared\Domain\ValueObjects\DateTime;
use Canalizador\Video\Domain\Entities\Video;
use Canalizador\Video\Domain\ValueObjects\GenerationId;
use Canalizador\Video\Domain\ValueObjects\Title;
use Canalizador\Video\Domain\ValueObjects\VideoId;

final readonly class VideoFactory
{
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
            createdAt: $createdAt ?? new DateTime(new \DateTimeImmutable()),
            generationId: $generationId,
        );
    }
}
