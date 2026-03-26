<?php

declare(strict_types = 1);

namespace Canalizador\YouTube\Transcription\Domain\Collections;

use Canalizador\Shared\Domain\Collection;
use Canalizador\Transcription\Domain\ValueObjects\Segmentation;

final class SegmentationCollection extends Collection
{
    protected function type(): string
    {
        return Segmentation::class;
    }
}
