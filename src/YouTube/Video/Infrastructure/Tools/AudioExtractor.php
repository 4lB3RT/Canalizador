<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Infrastructure\Tools;

use Canalizador\Shared\Domain\ValueObjects\LocalPath;
use Canalizador\YouTube\Video\Domain\Repositories\AudioExtractor as AudioExtractorRepository;
use Prism\Prism\Tool;

final class AudioExtractor extends Tool
{
    public function __construct(
        private readonly AudioExtractorRepository $audioExtractor,
    ) {
        parent::__construct();
        $this->as('AudioExtractor')
            ->for('Extract the audio from a video file and return the local file path of the extracted audio.')
            ->withStringParameter('videoPath', 'The local file path of the video file.')
            ->using($this);
    }

    public function __invoke(string $videoPath): string
    {
        $audioPath = $this->audioExtractor->extract(new LocalPath($videoPath));

        return $audioPath->value();
    }
}
