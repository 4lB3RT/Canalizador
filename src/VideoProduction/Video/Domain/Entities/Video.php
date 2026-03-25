<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Video\Domain\Entities;

use Canalizador\VideoProduction\Avatar\Domain\ValueObjects\AvatarId;
use Canalizador\YouTube\Channel\Domain\ValueObjects\ChannelId;
use Canalizador\VideoProduction\Script\Domain\Entities\Script;
use Canalizador\VideoProduction\Shared\Domain\ValueObjects\DateTime;
use Canalizador\VideoProduction\Shared\Domain\ValueObjects\LocalPath;
use Canalizador\VideoProduction\Video\Domain\ValueObjects\Description;
use Canalizador\VideoProduction\Video\Domain\ValueObjects\GenerationId;
use Canalizador\VideoProduction\Video\Domain\ValueObjects\Title;
use Canalizador\VideoProduction\Video\Domain\ValueObjects\VideoCategory;
use Canalizador\VideoProduction\Video\Domain\ValueObjects\VideoId;

final class Video
{
    public function __construct(
        private readonly VideoId $id,
        private readonly Script $script,
        private readonly ChannelId $channelId,
        private readonly Title $title,
        private readonly Description $description,
        private readonly VideoCategory $category,
        private readonly DateTime $createdAt,
        private readonly ?AvatarId $avatarId = null,
        private readonly ?GenerationId $generationId = null,
        private ?LocalPath $videoLocalPath = null,
        private ?DateTime $completedAt = null,
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

    public function channelId(): ChannelId
    {
        return $this->channelId;
    }

    public function avatarId(): ?AvatarId
    {
        return $this->avatarId;
    }

    public function title(): Title
    {
        return $this->title;
    }

    public function description(): Description
    {
        return $this->description;
    }

    public function category(): VideoCategory
    {
        return $this->category;
    }

    public function videoLocalPath(): ?LocalPath
    {
        return $this->videoLocalPath;
    }

    public function markAsCompleted(LocalPath $videoLocalPath, DateTime $completedAt): void
    {
        $this->videoLocalPath = $videoLocalPath;
        $this->completedAt = $completedAt;
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
            'channel_id' => $this->channelId->value(),
            'avatar_id' => $this->avatarId?->value(),
            'script' => $this->script->toArray(),
            'title' => $this->title->value(),
            'description' => $this->description->value(),
            'category' => $this->category->value,
            'generation_id' => $this->generationId?->value(),
            'video_local_path' => $this->videoLocalPath?->value(),
            'created_at' => $this->createdAt->value()->format('Y-m-d H:i:s'),
            'completed_at' => $this->completedAt?->value()->format('Y-m-d H:i:s'),
        ];
    }
}
