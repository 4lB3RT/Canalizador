<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Clip\Application\UseCases\DownloadClip;

use Canalizador\VideoProduction\Clip\Domain\Events\ClipCompleted;
use Canalizador\VideoProduction\Clip\Domain\Repositories\ClipDownloader;
use Canalizador\VideoProduction\Clip\Domain\Repositories\ClipRepository;
use Canalizador\VideoProduction\Clip\Domain\ValueObjects\ClipId;
use Canalizador\VideoProduction\Shared\Domain\Events\EventBus;
use Canalizador\VideoProduction\Shared\Domain\Services\Clock;
use Canalizador\VideoProduction\Shared\Domain\ValueObjects\LocalPath;

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

        try {
            $result = $this->clipDownloader->download($clip->generationId(), $outputPath);

            $clip->markAsCompleted($result['localPath'], $result['videoUri'], $this->clock->now());
            $this->clipRepository->save($clip);

            $this->eventBus->publish(
                new ClipCompleted($clip->id()->value(), $clip->videoId()->value(), $this->clock->now())
            );
        } catch (\Throwable $e) {
            $clip->markAsFailed();
            $this->clipRepository->save($clip);

            throw $e;
        }
    }
}
