<?php

declare(strict_types = 1);

namespace Canalizador\Video\Infrastructure\Tools;

use Canalizador\Transcription\Domain\Repositories\TranscriptionRepository;
use Canalizador\Video\Domain\Repositories\VideoRepository;
use Canalizador\Video\Domain\ValueObjects\VideoId;
use Prism\Prism\Tool;

final class AudioTranscription extends Tool
{
    public function __construct(
        private readonly VideoRepository         $videoRepository,
        private readonly TranscriptionRepository $transcriptionRepository,
    ) {
        parent::__construct();

        $this->as('AudioTranscription')
            ->for('Transcribe an audio file and return a structured JSON with segments and words with timestamps.')
            ->withStringParameter('videoId', 'The unique identifier of the video.')
            ->using($this);
    }

    public function __invoke(string $videoId): void
    {
        $videoId = VideoId::fromString($videoId);

        $video = $this->videoRepository->findById($videoId);

        if (!is_null($video->transcription())) {
            return;
        }

        $transcription = $this->transcriptionRepository->findByVideoId($videoId);

        $video->updateTranscription($transcription);

        $this->videoRepository->save($video);
    }
}
