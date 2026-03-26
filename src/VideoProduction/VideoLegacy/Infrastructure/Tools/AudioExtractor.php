<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\VideoLegacy\Infrastructure\Tools;

use Canalizador\Shared\Domain\ValueObjects\IntegerValue;
use Canalizador\VideoProduction\VideoLegacy\Application\UseCases\SaveAudio;
use Canalizador\VideoProduction\VideoLegacy\Domain\Exceptions\VideoNotFound;
use Canalizador\VideoProduction\VideoLegacy\Domain\ValueObjects\VideoId;
use Prism\Prism\Tool;

final class AudioExtractor extends Tool
{
    public function __construct(
        private readonly SaveAudio $saveAudio,
    ) {
        parent::__construct();

        $this->as('AudioExtractor')
            ->for('Extract the audio from a video file and return the local file path of the extracted audio.')
            ->withStringParameter('videoId', 'The unique identifier of the video.')
            ->using($this);
    }

    /* @throws VideoNotFound */
    public function __invoke(string $videoId): string
    {
        $videoId = VideoId::fromString($videoId);

        $audioLocalPath = $this->saveAudio->execute($videoId);

        return $audioLocalPath->value();
    }
}
