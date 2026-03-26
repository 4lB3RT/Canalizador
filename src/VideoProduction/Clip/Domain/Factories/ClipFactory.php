<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Clip\Domain\Factories;

use Canalizador\VideoProduction\Clip\Domain\Entities\Clip;
use Canalizador\VideoProduction\Clip\Domain\ValueObjects\ClipId;
use Canalizador\VideoProduction\Clip\Domain\ValueObjects\ClipStatus;
use Canalizador\VideoProduction\Clip\Domain\ValueObjects\Sequence;
use Canalizador\Shared\Domain\Services\Clock;
use Canalizador\Shared\Domain\ValueObjects\DateTime;
use Canalizador\Shared\Domain\ValueObjects\LocalPath;
use Canalizador\Shared\Domain\ValueObjects\Url;
use Canalizador\VideoProduction\Video\Domain\ValueObjects\GenerationId;
use Canalizador\VideoProduction\Video\Domain\ValueObjects\VideoId;

final readonly class ClipFactory
{
    public function __construct(
        private Clock $clock
    ) {
    }

    public function create(
        ClipId $id,
        VideoId $videoId,
        Sequence $sequence,
        GenerationId $generationId,
        ?string $script = null,
        ?DateTime $createdAt = null,
    ): Clip {
        return new Clip(
            id: $id,
            videoId: $videoId,
            sequence: $sequence,
            generationId: $generationId,
            status: ClipStatus::GENERATING,
            createdAt: $createdAt ?? $this->clock->now(),
            script: $script,
        );
    }

    public function createFromPrimitives(
        string $id,
        string $videoId,
        int $sequence,
        string $generationId,
        string $status,
        ?string $script = null,
        ?string $localPath = null,
        ?string $videoUri = null,
        ?DateTime $createdAt = null,
        ?DateTime $completedAt = null,
    ): Clip {
        return new Clip(
            id: ClipId::fromString($id),
            videoId: VideoId::fromString($videoId),
            sequence: Sequence::fromInt($sequence),
            generationId: GenerationId::fromString($generationId),
            status: ClipStatus::from($status),
            createdAt: $createdAt ?? $this->clock->now(),
            script: $script,
            localPath: $localPath !== null ? LocalPath::fromString($localPath) : null,
            videoUri: $videoUri !== null ? Url::fromString($videoUri) : null,
            completedAt: $completedAt,
        );
    }
}
