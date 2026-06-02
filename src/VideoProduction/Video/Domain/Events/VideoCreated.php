<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Video\Domain\Events;

use Helmreel\Shared\Shared\Domain\Events\DomainEvent;
use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\DateTime;

final readonly class VideoCreated implements DomainEvent
{
    public function __construct(
        private string $videoId,
        private DateTime $occurredAt,
    ) {
    }

    public function eventName(): string
    {
        return 'video.created';
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
