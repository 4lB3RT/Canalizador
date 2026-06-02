<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\VideoLegacy\Infrastructure\Tools;

use Helmreel\VideoProduction\VideoLegacy\Application\UseCases\SaveTranscription;
use Helmreel\VideoProduction\VideoLegacy\Domain\Exceptions\VideoLocalPathNotFound;
use Helmreel\VideoProduction\VideoLegacy\Domain\Exceptions\VideoNotFound;
use Helmreel\VideoProduction\VideoLegacy\Domain\ValueObjects\VideoId;
use Prism\Prism\Tool;

final class AudioTranscription extends Tool
{
    public function __construct(
        private readonly SaveTranscription $saveTranscription
    ) {
        parent::__construct();

        $this->as('AudioTranscription')
            ->for('Transcribe an audio file and return a structured JSON with segments and words with timestamps.')
            ->withStringParameter('videoId', 'The unique identifier of the video.')
            ->using($this);
    }

    /**
     * @throws VideoNotFound
     * @throws VideoLocalPathNotFound
     */
    public function __invoke(string $videoId): string
    {
        $videoId = VideoId::fromString($videoId);

        $transcription = $this->saveTranscription->execute($videoId);

        return json_encode($transcription->toArray(), JSON_PRETTY_PRINT);
    }
}
