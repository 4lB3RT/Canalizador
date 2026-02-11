<?php

declare(strict_types=1);

namespace Canalizador\Video\Application\UseCases\DownloadClip;

use Canalizador\Shared\Domain\Events\EventBus;
use Canalizador\Shared\Domain\Services\Clock;
use Canalizador\Shared\Domain\ValueObjects\LocalPath;
use Canalizador\Video\Domain\Events\ClipCompleted;
use Canalizador\Video\Domain\Repositories\ClipDownloader;
use Canalizador\Video\Domain\Repositories\ClipRepository;
use Canalizador\Video\Domain\ValueObjects\ClipId;

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
