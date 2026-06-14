<?php

declare(strict_types=1);

namespace Helmreel\Shared\Media\Domain\Entities;

use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\DateTime;
use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;
use Helmreel\Shared\Shared\Domain\ValueObjects\LocalPath;
use Helmreel\Shared\Media\Domain\ValueObjects\MediaId;
use Helmreel\Shared\Media\Domain\ValueObjects\MediaType;

final class Media
{
    public function __construct(
        private readonly MediaId $id,
        private readonly IntegerId $userId,
        private readonly MediaType $type,
        private readonly LocalPath $path,
        private readonly DateTime $createdAt,
        private ?DateTime $updatedAt = null,
    ) {
    }

    public function id(): MediaId
    {
        return $this->id;
    }

    public function userId(): IntegerId
    {
        return $this->userId;
    }

    public function type(): MediaType
    {
        return $this->type;
    }

    public function path(): LocalPath
    {
        return $this->path;
    }

    public function createdAt(): DateTime
    {
        return $this->createdAt;
    }

    public function updatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id->value(),
            'user_id' => $this->userId->value(),
            'type' => $this->type->value,
            'url' => '/video-production/media/' . $this->id->value(),
            'created_at' => $this->createdAt->value()->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->value()->format('Y-m-d H:i:s'),
        ];
    }
}
