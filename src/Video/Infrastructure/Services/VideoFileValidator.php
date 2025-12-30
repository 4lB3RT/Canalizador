<?php

declare(strict_types=1);

namespace Canalizador\Video\Infrastructure\Services;

use Canalizador\Video\Domain\Entities\Video;
use Canalizador\Video\Domain\Exceptions\VideoGenerationFailed;
use Canalizador\Video\Domain\Services\VideoFileValidator as VideoFileValidatorInterface;
use Canalizador\Video\Domain\Services\FileSystem;

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
