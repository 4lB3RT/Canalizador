<?php

declare(strict_types = 1);

namespace Helmreel\YouTube\Transcription\Domain\Collections;

use Helmreel\Shared\Shared\Domain\Collection;
use Helmreel\Transcription\Domain\ValueObjects\Segmentation;

final class SegmentationCollection extends Collection
{
    protected function type(): string
    {
        return Segmentation::class;
    }
}
