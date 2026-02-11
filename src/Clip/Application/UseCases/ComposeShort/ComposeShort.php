<?php

declare(strict_types=1);

namespace Canalizador\Clip\Application\UseCases\ComposeShort;

use Canalizador\Clip\Domain\Repositories\ClipRepository;
use Canalizador\Shared\Domain\Services\Clock;
use Canalizador\Shared\Domain\ValueObjects\LocalPath;
use Canalizador\Video\Domain\Repositories\VideoRepository;
use Canalizador\Video\Domain\ValueObjects\VideoId;
use Illuminate\Support\Facades\File;

final readonly class ComposeShort
{
    public function __construct(
        private ClipRepository $clipRepository,
        private VideoRepository $videoRepository,
        private Clock $clock,
    ) {
    }

    public function execute(ComposeShortRequest $request): void
    {
        $videoId = VideoId::fromString($request->videoId);
        $video = $this->videoRepository->findById($videoId);
        $clips = $this->clipRepository->findByVideoId($videoId)->sortedBySequence();

        $lastClip = $clips->last();

        $outputDir = storage_path('app/videos');
        if (!File::exists($outputDir)) {
            File::makeDirectory($outputDir, 0755, true);
        }

        $outputPath = $outputDir . "/{$videoId->value()}.mp4";

        File::copy($lastClip->localPath()->value(), $outputPath);

        $video->markAsCompleted(LocalPath::fromString($outputPath), $this->clock->now());
        $this->videoRepository->save($video);
    }
}
