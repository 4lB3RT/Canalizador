<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Infrastructure\Services\YouTube;

use Google_Service_YouTube_Video;
use Google_Service_YouTube_VideoSnippet;
use Google_Service_YouTube_VideoStatus;

interface YouTubeVideoBuilder
{
    public function buildVideoSnippet(string $title, string $description, array $tags): Google_Service_YouTube_VideoSnippet;

    public function buildVideoStatus(string $privacyStatus): Google_Service_YouTube_VideoStatus;

    public function buildVideo(
        Google_Service_YouTube_VideoSnippet $snippet,
        Google_Service_YouTube_VideoStatus $status
    ): Google_Service_YouTube_Video;
}
