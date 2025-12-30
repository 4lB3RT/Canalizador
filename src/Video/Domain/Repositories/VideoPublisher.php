<?php

declare(strict_types=1);

namespace Canalizador\Video\Domain\Repositories;

use Canalizador\Video\Domain\Entities\Video;
use Canalizador\Video\Domain\Exceptions\VideoGenerationFailed;

interface VideoPublisher
{
    /**
     * @throws VideoGenerationFailed
     */
    public function publish(
        Video $video
    ): string;
}
