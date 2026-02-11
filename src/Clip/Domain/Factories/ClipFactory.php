<?php

declare(strict_types=1);

namespace Canalizador\Clip\Domain\Factories;

use Canalizador\Clip\Domain\Entities\Clip;
use Canalizador\Clip\Domain\ValueObjects\ClipId;
use Canalizador\Clip\Domain\ValueObjects\ClipStatus;
use Canalizador\Clip\Domain\ValueObjects\Sequence;
use Canalizador\Shared\Domain\Services\Clock;
use Canalizador\Shared\Domain\ValueObjects\DateTime;
use Canalizador\Shared\Domain\ValueObjects\LocalPath;
use Canalizador\Shared\Domain\ValueObjects\Url;
use Canalizador\Video\Domain\ValueObjects\GenerationId;
use Canalizador\Video\Domain\ValueObjects\VideoId;

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
        ?DateTime $createdAt = null,
    ): Clip {
        return new Clip(
            id: $id,
            videoId: $videoId,
            sequence: $sequence,
            generationId: $generationId,
            status: ClipStatus::GENERATING,
            createdAt: $createdAt ?? $this->clock->now(),
        );
    }

    public function createFromPrimitives(
        string $id,
        string $videoId,
        int $sequence,
        string $generationId,
        string $status,
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
            localPath: $localPath !== null ? LocalPath::fromString($localPath) : null,
            videoUri: $videoUri !== null ? Url::fromString($videoUri) : null,
            completedAt: $completedAt,
        );
    }
}
