<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Domain\Repositories;

use Canalizador\YouTube\Video\Domain\Exceptions\YouTubeOperationFailed;
use Canalizador\YouTube\Video\Domain\ValueObjects\VideoToPublish;

interface VideoPublisher
{
    /**
     * @throws YouTubeOperationFailed
     */
    public function publish(VideoToPublish $video): string;
}
