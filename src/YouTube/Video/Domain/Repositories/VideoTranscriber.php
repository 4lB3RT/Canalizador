<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Domain\Repositories;

use Canalizador\YouTube\Video\Domain\ValueObjects\AudioPath;

interface VideoTranscriber
{
    /**
     * @return array<int, array{start: float, end: float, text: string}> segments with timestamps
     */
    public function transcribe(AudioPath $audioPath): array;
}
