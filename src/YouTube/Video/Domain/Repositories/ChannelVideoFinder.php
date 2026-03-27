<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Domain\Repositories;

use Canalizador\YouTube\Video\Domain\Exceptions\YouTubeOperationFailed;
use Canalizador\YouTube\Video\Domain\ValueObjects\YouTubeVideoId;

interface ChannelVideoFinder
{
    /**
     * @throws YouTubeOperationFailed
     */
    public function findLatestByChannelId(string $channelId): YouTubeVideoId;
}
