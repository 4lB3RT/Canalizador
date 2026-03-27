<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Domain\Repositories;

use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\LocalPath;
use Canalizador\YouTube\Video\Domain\Exceptions\YouTubeOperationFailed;
use Canalizador\YouTube\Video\Domain\ValueObjects\YouTubeVideoId;

interface VideoDownloader
{
    /**
     * @throws YouTubeOperationFailed
     */
    public function download(YouTubeVideoId $videoId): LocalPath;
}
