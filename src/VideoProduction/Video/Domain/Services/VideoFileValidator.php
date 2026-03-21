<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Video\Domain\Services;

use Canalizador\VideoProduction\Video\Domain\Entities\Video;
use Canalizador\VideoProduction\Video\Domain\Exceptions\VideoGenerationFailed;

interface VideoFileValidator
{
    /**
     * @throws VideoGenerationFailed
     */
    public function validate(Video $video): void;
}
