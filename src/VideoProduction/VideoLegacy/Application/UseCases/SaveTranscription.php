<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\VideoLegacy\Application\UseCases;

use Helmreel\VideoProduction\VideoLegacy\Domain\Exceptions\VideoLocalPathNotFound;
use Helmreel\VideoProduction\VideoLegacy\Domain\Exceptions\VideoNotFound;
use Helmreel\VideoProduction\VideoLegacy\Domain\Repositories\VideoRepository;
use Helmreel\VideoProduction\VideoLegacy\Domain\ValueObjects\VideoId;
use Helmreel\YouTube\Transcription\Domain\Entities\Transcription;
use Helmreel\YouTube\Transcription\Domain\Repositories\TranscriptionRepository;

final readonly class SaveTranscription
{
    public function __construct(
        private VideoRepository         $videoRepository,
        private TranscriptionRepository $transcriptionRepository,
    ) {
    }

    /**
     * @throws VideoNotFound
     * @throws VideoLocalPathNotFound
     */
    public function execute(VideoId $videoId): ?Transcription
    {
        $video = $this->videoRepository->findById(videoId: $videoId);

        if ($video === null) {
            throw VideoNotFound::default();
        }

        if (!is_null($video->transcription())) {
            return $video->transcription();
        }

        $transcription = $this->transcriptionRepository->findByVideo($video);

        $video->updateTranscription($transcription);

        $this->videoRepository->save($video);

        return $video->transcription();
    }
}
