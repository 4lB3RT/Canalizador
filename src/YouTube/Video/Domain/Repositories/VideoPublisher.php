<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Domain\Repositories;

use Canalizador\YouTube\Video\Domain\Entities\Video;
use Canalizador\YouTube\Video\Domain\Exceptions\YouTubeOperationFailed;

interface VideoPublisher
{
    /**
     * @throws YouTubeOperationFailed
     */
    public function publish(Video $video): string;
}
