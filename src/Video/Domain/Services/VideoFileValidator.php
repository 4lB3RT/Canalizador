<?php

declare(strict_types=1);

namespace Canalizador\Video\Domain\Services;

use Canalizador\Video\Domain\Entities\Video;
use Canalizador\Video\Domain\Exceptions\VideoGenerationFailed;

interface VideoFileValidator
{
    /**
     * @throws VideoGenerationFailed
     */
    public function validate(Video $video): void;
}
