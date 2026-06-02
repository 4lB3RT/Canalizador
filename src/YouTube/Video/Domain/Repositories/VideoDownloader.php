<?php

declare(strict_types=1);

namespace Helmreel\YouTube\Video\Domain\Repositories;

use Helmreel\Shared\Shared\Domain\ValueObjects\LocalPath;
use Helmreel\YouTube\Video\Domain\Exceptions\YouTubeOperationFailed;
use Helmreel\YouTube\Video\Domain\ValueObjects\YouTubeVideoId;

interface VideoDownloader
{
    /**
     * @throws YouTubeOperationFailed
     */
    public function download(YouTubeVideoId $videoId): LocalPath;
}
