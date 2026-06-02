<?php

declare(strict_types=1);

namespace Helmreel\YouTube\Video\Domain\Repositories;

use Helmreel\Shared\Shared\Domain\ValueObjects\LocalPath;
use Helmreel\YouTube\Video\Domain\Exceptions\VideoFragmentationFailed;

interface VideoFragmenter
{
    /**
     * @return LocalPath[] ordenados por índice
     * @throws VideoFragmentationFailed
     */
    public function fragment(LocalPath $videoPath, int $segmentDurationSeconds): array;

    /**
     * @throws VideoFragmentationFailed
     */
    public function fragmentAt(LocalPath $videoPath, int $startSeconds, int $durationSeconds): LocalPath;
}
