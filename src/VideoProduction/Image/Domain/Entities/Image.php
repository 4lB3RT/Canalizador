<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Image\Domain\Entities;

use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\DateTime;
use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;
use Helmreel\Shared\Shared\Domain\ValueObjects\LocalPath;
use Helmreel\VideoProduction\Image\Domain\ValueObjects\ImageId;

final class Image
{
    public function __construct(
        private readonly ImageId $id,
        private readonly IntegerId $userId,
        private readonly LocalPath $path,
        private readonly DateTime $createdAt,
        private ?DateTime $updatedAt = null,
    ) {
    }

    public function id(): ImageId
    {
        return $this->id;
    }

    public function userId(): IntegerId
    {
        return $this->userId;
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
            'path' => $this->path->value(),
            'created_at' => $this->createdAt->value()->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->value()->format('Y-m-d H:i:s'),
        ];
    }
}
