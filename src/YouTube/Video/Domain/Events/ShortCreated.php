<?php

declare(strict_types = 1);

namespace Canalizador\YouTube\Video\Domain\Events;

use Canalizador\Shared\Shared\Domain\Events\DomainEvent;
use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\DateTime;

final readonly class ShortCreated implements DomainEvent
{
    public function __construct(
        private string   $videoId,
        private string   $parentId,
        private DateTime $occurredAt,
    ) {
    }

    public function eventName(): string
    {
        return 'youtube.short.created';
    }

    public function occurredAt(): DateTime
    {
        return $this->occurredAt;
    }

    public function payload(): array
    {
        return [
            'video_id'  => $this->videoId,
            'parent_id' => $this->parentId,
        ];
    }

    public function videoId(): string
    {
        return $this->videoId;
    }

    public function parentId(): string
    {
        return $this->parentId;
    }
}
