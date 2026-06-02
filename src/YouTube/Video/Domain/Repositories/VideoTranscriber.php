<?php

declare(strict_types=1);

namespace Helmreel\YouTube\Video\Domain\Repositories;

use Helmreel\YouTube\Video\Domain\ValueObjects\AudioPath;

interface VideoTranscriber
{
    /**
     * @return array<int, array{start: float, end: float, text: string}> segments with timestamps
     */
    public function transcribe(AudioPath $audioPath): array;
}
