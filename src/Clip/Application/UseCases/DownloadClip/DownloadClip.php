<?php

declare(strict_types=1);

namespace Canalizador\Clip\Application\UseCases\DownloadClip;

use Canalizador\Clip\Domain\Events\ClipCompleted;
use Canalizador\Clip\Domain\Repositories\ClipDownloader;
use Canalizador\Clip\Domain\Repositories\ClipRepository;
use Canalizador\Clip\Domain\ValueObjects\ClipId;
use Canalizador\Shared\Domain\Events\EventBus;
use Canalizador\Shared\Domain\Services\Clock;
use Canalizador\Shared\Domain\ValueObjects\LocalPath;

final readonly class DownloadClip
{
    public function __construct(
        private ClipRepository $clipRepository,
        private ClipDownloader $clipDownloader,
        private EventBus $eventBus,
        private Clock $clock,
    ) {
    }

    public function execute(DownloadClipRequest $request): void
    {
        $clip = $this->clipRepository->findById(ClipId::fromString($request->clipId));

        $outputPath = LocalPath::fromString(
            storage_path("app/clips/{$clip->id()->value()}.mp4")
        );

        $result = $this->clipDownloader->download($clip->generationId(), $outputPath);

        $clip->markAsCompleted($result['localPath'], $result['videoUri'], $this->clock->now());
        $this->clipRepository->save($clip);

        $this->eventBus->publish(
            new ClipCompleted($clip->id()->value(), $clip->videoId()->value(), $this->clock->now())
        );
    }
}
