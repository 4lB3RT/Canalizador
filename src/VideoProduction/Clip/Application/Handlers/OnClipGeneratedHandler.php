<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Clip\Application\Handlers;

use Helmreel\Shared\Shared\Domain\Events\DomainEvent;
use Helmreel\Shared\Shared\Domain\Events\DomainEventHandler;
use Helmreel\VideoProduction\Clip\Application\UseCases\DownloadClip\DownloadClip;
use Helmreel\VideoProduction\Clip\Application\UseCases\DownloadClip\DownloadClipRequest;
use Helmreel\VideoProduction\Clip\Domain\Events\ClipGenerated;
use Helmreel\VideoProduction\Video\Domain\Exceptions\VideoGenerationFailed;
use Illuminate\Support\Facades\Log;

final readonly class OnClipGeneratedHandler implements DomainEventHandler
{
    public function __construct(
        private DownloadClip $downloadClip
    ) {
    }

    public function handle(DomainEvent $event): void
    {
        assert($event instanceof ClipGenerated);

        try {
            $this->downloadClip->execute(
                new DownloadClipRequest(clipId: $event->clipId())
            );
        } catch (VideoGenerationFailed $e) {
            Log::error('Clip generation failed permanently, not retrying: ' . $e->getMessage());
        }
    }
}
