<?php

declare(strict_types = 1);

namespace Canalizador\YouTube\Video\Domain\Events;

use Canalizador\Shared\Shared\Domain\Events\DomainEvent;
use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\DateTime;

final readonly class ShortGenerated implements DomainEvent
{
    public function __construct(
        private string   $shortId,
        private string   $parentVideoId,
        private DateTime $occurredAt,
    ) {
    }

    public function eventName(): string
    {
        return 'youtube.short.generated';
    }

    public function occurredAt(): DateTime
    {
        return $this->occurredAt;
    }

    public function payload(): array
    {
        return [
            'short_id'        => $this->shortId,
            'parent_video_id' => $this->parentVideoId,
        ];
    }

    public function shortId(): string
    {
        return $this->shortId;
    }

    public function parentVideoId(): string
    {
        return $this->parentVideoId;
    }
}
