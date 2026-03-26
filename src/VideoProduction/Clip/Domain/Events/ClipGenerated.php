<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Clip\Domain\Events;

use Canalizador\Shared\Domain\Events\DomainEvent;
use Canalizador\Shared\Domain\ValueObjects\DateTime;

final readonly class ClipGenerated implements DomainEvent
{
    public function __construct(
        private string $clipId,
        private string $videoId,
        private DateTime $occurredAt,
    ) {
    }

    public function eventName(): string
    {
        return 'clip.generated';
    }

    public function occurredAt(): DateTime
    {
        return $this->occurredAt;
    }

    public function payload(): array
    {
        return [
            'clip_id' => $this->clipId,
            'video_id' => $this->videoId,
        ];
    }

    public function clipId(): string
    {
        return $this->clipId;
    }

    public function videoId(): string
    {
        return $this->videoId;
    }
}
