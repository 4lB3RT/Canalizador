<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Clip\Application\UseCases\DownloadClip;

use Helmreel\Shared\Shared\Domain\Events\EventBus;
use Helmreel\Shared\Shared\Domain\Services\Clock;
use Helmreel\Shared\Shared\Domain\ValueObjects\LocalPath;
use Helmreel\VideoProduction\Clip\Domain\Events\ClipCompleted;
use Helmreel\VideoProduction\Clip\Domain\Repositories\ClipDownloader;
use Helmreel\VideoProduction\Clip\Domain\Repositories\ClipRepository;
use Helmreel\VideoProduction\Clip\Domain\Services\VideoComposer;
use Helmreel\VideoProduction\Clip\Domain\ValueObjects\ClipId;

final readonly class DownloadClip
{
    public function __construct(
        private ClipRepository $clipRepository,
        private ClipDownloader $clipDownloader,
        private VideoComposer $videoComposer,
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

            $lastFramePath = LocalPath::fromString(
                storage_path("app/clips/{$clip->id()->value()}_lastframe.jpg")
            );
            $this->videoComposer->extractLastFrame($result['localPath']->value(), $lastFramePath->value());

            $clip->markAsCompleted($result['localPath'], $result['videoUri'], $this->clock->now(), $lastFramePath);
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
