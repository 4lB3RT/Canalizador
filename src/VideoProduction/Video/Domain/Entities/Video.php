<?php

declare(strict_types = 1);

namespace Helmreel\VideoProduction\Video\Domain\Entities;

use Helmreel\Shared\Shared\Domain\ValueObjects\Description;
use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\DateTime;
use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;
use Helmreel\Shared\Shared\Domain\ValueObjects\LocalPath;
use Helmreel\Shared\Shared\Domain\ValueObjects\Title;
use Helmreel\VideoProduction\Avatar\Domain\ValueObjects\AvatarId;
use Helmreel\VideoProduction\Media\Domain\ValueObjects\MediaId;
use Helmreel\VideoProduction\Script\Domain\Entities\Script;
use Helmreel\VideoProduction\Video\Domain\ValueObjects\GenerationId;
use Helmreel\VideoProduction\Video\Domain\ValueObjects\Resolution;
use Helmreel\VideoProduction\Video\Domain\ValueObjects\VideoCategory;
use Helmreel\VideoProduction\Video\Domain\ValueObjects\VideoId;

final class Video
{
    public function __construct(
        private readonly VideoId $id,
        private readonly IntegerId $userId,
        private readonly Script $script,
        private readonly Title $title,
        private readonly Description $description,
        private readonly VideoCategory $category,
        private readonly DateTime $createdAt,
        private readonly Resolution $resolution = Resolution::HD,
        private readonly ?AvatarId $avatarId = null,
        private readonly ?GenerationId $generationId = null,
        private ?LocalPath $videoLocalPath = null,
        private ?DateTime $completedAt = null,
        private ?MediaId $mediaId = null,
    ) {
    }

    public function id(): VideoId
    {
        return $this->id;
    }

    public function userId(): IntegerId
    {
        return $this->userId;
    }

    public function script(): Script
    {
        return $this->script;
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

    public function resolution(): Resolution
    {
        return $this->resolution;
    }

    public function videoLocalPath(): ?LocalPath
    {
        return $this->videoLocalPath;
    }

    public function mediaId(): ?MediaId
    {
        return $this->mediaId;
    }

    public function markAsCompleted(LocalPath $videoLocalPath, DateTime $completedAt, ?MediaId $mediaId = null): void
    {
        $this->videoLocalPath = $videoLocalPath;
        $this->completedAt    = $completedAt;
        if ($mediaId !== null) {
            $this->mediaId = $mediaId;
        }
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
        $completed = $this->completedAt !== null;

        return [
            'id'            => $this->id->value(),
            'user_id'       => $this->userId->value(),
            'script_id'     => $this->script->id()->value(),
            'avatar_id'     => $this->avatarId?->value(),
            'title'         => $this->title->value(),
            'description'   => $this->description->value(),
            'category'      => $this->category->value,
            'resolution'    => $this->resolution->value,
            'status'        => $completed ? 'completed' : 'processing',
            'video_url'     => $this->mediaId !== null ? '/video-production/media/' . $this->mediaId->value() : null,
            'created_at'    => $this->createdAt->value()->format('Y-m-d H:i:s'),
            'completed_at'  => $this->completedAt?->value()->format('Y-m-d H:i:s'),
        ];
    }
}
