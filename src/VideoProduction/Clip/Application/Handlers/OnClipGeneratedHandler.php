<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Clip\Application\Handlers;

use Canalizador\VideoProduction\Clip\Application\UseCases\DownloadClip\DownloadClip;
use Canalizador\VideoProduction\Clip\Application\UseCases\DownloadClip\DownloadClipRequest;
use Canalizador\VideoProduction\Clip\Domain\Events\ClipGenerated;
use Canalizador\VideoProduction\Shared\Domain\Events\DomainEvent;
use Canalizador\VideoProduction\Shared\Domain\Events\DomainEventHandler;

final readonly class OnClipGeneratedHandler implements DomainEventHandler
{
    public function __construct(
        private DownloadClip $downloadClip
    ) {
    }

    public function handle(DomainEvent $event): void
    {
        assert($event instanceof ClipGenerated);

        $this->downloadClip->execute(
            new DownloadClipRequest(clipId: $event->clipId())
        );
    }
}
