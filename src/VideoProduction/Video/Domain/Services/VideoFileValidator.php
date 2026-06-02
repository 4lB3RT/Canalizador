<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Video\Domain\Services;

use Helmreel\VideoProduction\Video\Domain\Entities\Video;
use Helmreel\VideoProduction\Video\Domain\Exceptions\VideoGenerationFailed;

interface VideoFileValidator
{
    /**
     * @throws VideoGenerationFailed
     */
    public function validate(Video $video): void;
}
