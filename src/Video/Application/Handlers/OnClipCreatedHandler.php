<?php

declare(strict_types=1);

namespace Canalizador\Video\Application\Handlers;

use Canalizador\Shared\Domain\Events\DomainEvent;
use Canalizador\Shared\Domain\Events\DomainEventHandler;
use Canalizador\Video\Application\UseCases\DownloadClip\DownloadClip;
use Canalizador\Video\Application\UseCases\DownloadClip\DownloadClipRequest;
use Canalizador\Video\Domain\Events\ClipCreated;

final readonly class OnClipCreatedHandler implements DomainEventHandler
{
    public function __construct(
        private DownloadClip $downloadClip
    ) {
    }

    public function handle(DomainEvent $event): void
    {
        assert($event instanceof ClipCreated);

        $this->downloadClip->execute(
            new DownloadClipRequest(clipId: $event->clipId())
        );
    }
}
