<?php

declare(strict_types = 1);

namespace Canalizador\VideoLegacy\Application\UseCases;

use Canalizador\Transcription\Domain\Entities\Transcription;
use Canalizador\Transcription\Domain\Repositories\TranscriptionRepository;
use Canalizador\VideoLegacy\Domain\Exceptions\VideoLocalPathNotFound;
use Canalizador\VideoLegacy\Domain\Exceptions\VideoNotFound;
use Canalizador\VideoLegacy\Domain\Repositories\VideoRepository;
use Canalizador\VideoLegacy\Domain\ValueObjects\VideoId;

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
