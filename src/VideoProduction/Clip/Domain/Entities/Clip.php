<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Clip\Domain\Entities;

use Canalizador\VideoProduction\Clip\Domain\ValueObjects\ClipId;
use Canalizador\VideoProduction\Clip\Domain\ValueObjects\ClipStatus;
use Canalizador\VideoProduction\Clip\Domain\ValueObjects\Sequence;
use Canalizador\Shared\Domain\ValueObjects\DateTime;
use Canalizador\Shared\Domain\ValueObjects\LocalPath;
use Canalizador\Shared\Domain\ValueObjects\Url;
use Canalizador\VideoProduction\Video\Domain\ValueObjects\GenerationId;
use Canalizador\VideoProduction\Video\Domain\ValueObjects\VideoId;

final class Clip
{
    public const int TOTAL_CLIPS = 2;

    public function __construct(
        private readonly ClipId $id,
        private readonly VideoId $videoId,
        private readonly Sequence $sequence,
        private GenerationId $generationId,
        private ClipStatus $status,
        private readonly DateTime $createdAt,
        private ?string $script = null,
        private ?LocalPath $localPath = null,
        private ?Url $videoUri = null,
        private ?DateTime $completedAt = null,
    ) {
    }

    public function id(): ClipId
    {
        return $this->id;
    }

    public function videoId(): VideoId
    {
        return $this->videoId;
    }

    public function sequence(): Sequence
    {
        return $this->sequence;
    }

    public function generationId(): GenerationId
    {
        return $this->generationId;
    }

    public function status(): ClipStatus
    {
        return $this->status;
    }

    public function localPath(): ?LocalPath
    {
        return $this->localPath;
    }

    public function videoUri(): ?Url
    {
        return $this->videoUri;
    }

    public function createdAt(): DateTime
    {
        return $this->createdAt;
    }

    public function script(): ?string
    {
        return $this->script;
    }

    public function completedAt(): ?DateTime
    {
        return $this->completedAt;
    }

    public function markAsCompleted(LocalPath $localPath, Url $videoUri, DateTime $completedAt): void
    {
        $this->status = ClipStatus::COMPLETED;
        $this->localPath = $localPath;
        $this->videoUri = $videoUri;
        $this->completedAt = $completedAt;
    }

    public function updateGenerationId(GenerationId $generationId): void
    {
        $this->generationId = $generationId;
    }

    public function markAsFailed(): void
    {
        $this->status = ClipStatus::FAILED;
    }

    public function isCompleted(): bool
    {
        return $this->status === ClipStatus::COMPLETED;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id->value(),
            'video_id' => $this->videoId->value(),
            'sequence' => $this->sequence->value(),
            'generation_id' => $this->generationId->value(),
            'script' => $this->script,
            'status' => $this->status->value,
            'local_path' => $this->localPath?->value(),
            'video_uri' => $this->videoUri?->value(),
            'created_at' => $this->createdAt->value()->format('Y-m-d H:i:s'),
            'completed_at' => $this->completedAt?->value()->format('Y-m-d H:i:s'),
        ];
    }
}
