<?php

declare(strict_types=1);

namespace Helmreel\YouTube\Video\Domain\Repositories;

use Helmreel\YouTube\Video\Domain\Entities\Video;
use Helmreel\YouTube\Video\Domain\Exceptions\YouTubeOperationFailed;

interface VideoPublisher
{
    /**
     * @throws YouTubeOperationFailed
     */
    public function publish(Video $video): void;
}
