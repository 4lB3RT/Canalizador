<?php

declare(strict_types=1);

namespace Canalizador\Video\Domain\Entities;

use Canalizador\Script\Domain\Entities\Script;
use Canalizador\Shared\Domain\ValueObjects\DateTime;
use Canalizador\Shared\Domain\ValueObjects\LocalPath;
use Canalizador\Video\Domain\ValueObjects\Description;
use Canalizador\Video\Domain\ValueObjects\GenerationId;
use Canalizador\Video\Domain\ValueObjects\Title;
use Canalizador\Video\Domain\ValueObjects\VideoId;

final class Video
{
    public function __construct(
        private readonly VideoId $id,
        private readonly Script $script,
        private readonly Title $title,
        private readonly Description $description,
        private readonly DateTime $createdAt,
        private readonly ?GenerationId $generationId = null,
        private ?LocalPath $videoLocalPath = null,
        private readonly ?DateTime $completedAt = null,
    ) {
    }

    public function id(): VideoId
    {
        return $this->id;
    }

    public function script(): Script
    {
        return $this->script;
    }

    public function title(): Title
    {
        return $this->title;
    }

    public function description(): Description
    {
        return $this->description;
    }

    public function videoLocalPath(): ?LocalPath
    {
        return $this->videoLocalPath;
    }

    public function updateVideoLocalPath(LocalPath $videoLocalPath): void
    {
        $this->videoLocalPath = $videoLocalPath;

    }

    public function createdAt(): DateTime
    {
        return $this->createdAt;
    }

    public function completedAt(): ?DateTime
    {
        return $this->completedAt;
    }

    public function generationId(): ?GenerationId
    {
        return $this->generationId;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id->value(),
            'script_id' => $this->script->id()->value(),
            'script' => $this->script->toArray(),
            'title' => $this->title->value(),
            'description' => $this->description->value(),
            'generation_id' => $this->generationId?->value(),
            'video_local_path' => $this->videoLocalPath?->value(),
            'created_at' => $this->createdAt->value()->format('Y-m-d H:i:s'),
            'completed_at' => $this->completedAt?->value()->format('Y-m-d H:i:s'),
        ];
    }
}
