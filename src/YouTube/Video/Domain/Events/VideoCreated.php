<?php

declare(strict_types = 1);

namespace Canalizador\YouTube\Video\Domain\Events;

use Canalizador\Shared\Shared\Domain\Events\DomainEvent;
use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\DateTime;

final readonly class VideoCreated implements DomainEvent
{
    public function __construct(
        private string   $videoId,
        private string   $platformId,
        private DateTime $occurredAt,
    ) {
    }

    public function eventName(): string
    {
        return 'youtube.video.created';
    }

    public function occurredAt(): DateTime
    {
        return $this->occurredAt;
    }

    public function payload(): array
    {
        return [
            'video_id'    => $this->videoId,
            'platform_id' => $this->platformId,
        ];
    }

    public function videoId(): string
    {
        return $this->videoId;
    }

    public function platformId(): string
    {
        return $this->platformId;
    }
}
