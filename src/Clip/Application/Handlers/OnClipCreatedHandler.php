<?php

declare(strict_types=1);

namespace Canalizador\Clip\Application\Handlers;

use Canalizador\Clip\Application\UseCases\DownloadClip\DownloadClip;
use Canalizador\Clip\Application\UseCases\DownloadClip\DownloadClipRequest;
use Canalizador\Clip\Domain\Events\ClipCreated;
use Canalizador\Shared\Domain\Events\DomainEvent;
use Canalizador\Shared\Domain\Events\DomainEventHandler;

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
