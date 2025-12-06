<?php

declare(strict_types = 1);

namespace Canalizador\Video\Domain\Entities;

use Canalizador\Script\Domain\ValueObjects\ScriptId;
use Canalizador\Shared\Domain\ValueObjects\DateTime;
use Canalizador\Shared\Domain\ValueObjects\LocalPath;
use Canalizador\Video\Domain\ValueObjects\Title;
use Canalizador\Video\Domain\ValueObjects\VideoId;

final readonly class Video
{
    public function __construct(
        private VideoId $id,
        private ScriptId $scriptId,
        private Title $title,
        private DateTime $createdAt,
        private ?LocalPath $videoLocalPath = null,
        private ?LocalPath $audioLocalPath = null,
        private ?DateTime $completedAt = null,
    ) {
    }

    public function id(): VideoId
    {
        return $this->id;
    }

    public function scriptId(): ScriptId
    {
        return $this->scriptId;
    }

    public function title(): Title
    {
        return $this->title;
    }

    public function videoLocalPath(): ?LocalPath
    {
        return $this->videoLocalPath;
    }

    public function audioLocalPath(): ?LocalPath
    {
        return $this->audioLocalPath;
    }

    public function createdAt(): DateTime
    {
        return $this->createdAt;
    }

    public function completedAt(): ?DateTime
    {
        return $this->completedAt;
    }

    public function withVideoLocalPath(LocalPath $videoLocalPath): self
    {
        return new self(
            id: $this->id,
            scriptId: $this->scriptId,
            title: $this->title,
            createdAt: $this->createdAt,
            videoLocalPath: $videoLocalPath,
            audioLocalPath: $this->audioLocalPath,
            completedAt: $this->completedAt,
        );
    }

    public function withAudioLocalPath(LocalPath $audioLocalPath): self
    {
        return new self(
            id: $this->id,
            scriptId: $this->scriptId,
            title: $this->title,
            createdAt: $this->createdAt,
            videoLocalPath: $this->videoLocalPath,
            audioLocalPath: $audioLocalPath,
            completedAt: $this->completedAt,
        );
    }

    public function markAsCompleted(DateTime $completedAt): self
    {
        return new self(
            id: $this->id,
            scriptId: $this->scriptId,
            title: $this->title,
            createdAt: $this->createdAt,
            videoLocalPath: $this->videoLocalPath,
            audioLocalPath: $this->audioLocalPath,
            completedAt: $completedAt,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id->value(),
            'script_id' => $this->scriptId->value(),
            'title' => $this->title->value(),
            'video_local_path' => $this->videoLocalPath?->value(),
            'audio_local_path' => $this->audioLocalPath?->value(),
            'created_at' => $this->createdAt->value()->format('Y-m-d H:i:s'),
            'completed_at' => $this->completedAt?->value()->format('Y-m-d H:i:s'),
        ];
    }
}
