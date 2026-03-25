<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Domain\Repositories;

use Canalizador\Shared\Domain\ValueObjects\LocalPath;
use Canalizador\YouTube\Video\Domain\Exceptions\VideoFragmentationFailed;

interface VideoFragmenter
{
    /**
     * @return LocalPath[] ordenados por índice
     * @throws VideoFragmentationFailed
     */
    public function fragment(LocalPath $videoPath, int $segmentDurationSeconds): array;
}
