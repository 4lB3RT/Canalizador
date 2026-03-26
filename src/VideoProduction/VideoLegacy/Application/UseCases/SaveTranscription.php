<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\VideoLegacy\Application\UseCases;

use Canalizador\VideoProduction\VideoLegacy\Domain\Exceptions\VideoLocalPathNotFound;
use Canalizador\VideoProduction\VideoLegacy\Domain\Exceptions\VideoNotFound;
use Canalizador\VideoProduction\VideoLegacy\Domain\Repositories\VideoRepository;
use Canalizador\VideoProduction\VideoLegacy\Domain\ValueObjects\VideoId;
use Canalizador\YouTube\Transcription\Domain\Entities\Transcription;
use Canalizador\YouTube\Transcription\Domain\Repositories\TranscriptionRepository;

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
