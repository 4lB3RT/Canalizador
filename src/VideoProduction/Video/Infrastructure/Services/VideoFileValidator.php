<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Video\Infrastructure\Services;

use Helmreel\VideoProduction\Video\Domain\Entities\Video;
use Helmreel\VideoProduction\Video\Domain\Exceptions\VideoGenerationFailed;
use Helmreel\VideoProduction\Video\Domain\Services\VideoFileValidator as VideoFileValidatorInterface;
use Helmreel\VideoProduction\Video\Domain\Services\FileSystem;

final readonly class VideoFileValidator implements VideoFileValidatorInterface
{
    public function __construct(
        private FileSystem $fileSystem
    ) {
    }

    /**
     * @throws VideoGenerationFailed
     */
    public function validate(Video $video): void
    {
        $videoLocalPath = $video->videoLocalPath();
        if ($videoLocalPath === null) {
            throw VideoGenerationFailed::apiError(
                "Video local path is not set for video ID: {$video->id()->value()}"
            );
        }

        if (!$this->fileSystem->exists($videoLocalPath->value())) {
            throw VideoGenerationFailed::apiError("Video file not found: {$videoLocalPath->value()}");
        }
    }
}
