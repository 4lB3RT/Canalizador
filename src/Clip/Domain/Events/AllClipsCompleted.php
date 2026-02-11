<?php

declare(strict_types=1);

namespace Canalizador\Clip\Domain\Events;

use Canalizador\Shared\Domain\Events\DomainEvent;
use Canalizador\Shared\Domain\ValueObjects\DateTime;

final readonly class AllClipsCompleted implements DomainEvent
{
    public function __construct(
        private string $videoId,
        private DateTime $occurredAt,
    ) {
    }

    public function eventName(): string
    {
        return 'video.all_clips_completed';
    }

    public function occurredAt(): DateTime
    {
        return $this->occurredAt;
    }

    public function payload(): array
    {
        return [
            'video_id' => $this->videoId,
        ];
    }

    public function videoId(): string
    {
        return $this->videoId;
    }
}
