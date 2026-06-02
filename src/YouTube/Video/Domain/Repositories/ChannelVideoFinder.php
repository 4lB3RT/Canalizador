<?php

declare(strict_types=1);

namespace Helmreel\YouTube\Video\Domain\Repositories;

use Helmreel\YouTube\Video\Domain\Exceptions\YouTubeOperationFailed;
use Helmreel\YouTube\Video\Domain\ValueObjects\YouTubeVideoId;

interface ChannelVideoFinder
{
    /**
     * @throws YouTubeOperationFailed
     */
    public function findLatestByChannelId(string $channelId): YouTubeVideoId;
}
