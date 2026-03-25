<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Infrastructure\Services\YouTube;

use Google_Service_YouTube_Video;
use Google_Service_YouTube_VideoSnippet;
use Google_Service_YouTube_VideoStatus;

final class GoogleYouTubeVideoBuilder implements YouTubeVideoBuilder
{
    public function buildVideoSnippet(string $title, string $description, array $tags): Google_Service_YouTube_VideoSnippet
    {
        $snippet = new Google_Service_YouTube_VideoSnippet();
        $snippet->setTitle($title);
        $snippet->setDescription($description);
        $snippet->setTags($tags);

        return $snippet;
    }

    public function buildVideoStatus(string $privacyStatus): Google_Service_YouTube_VideoStatus
    {
        $status = new Google_Service_YouTube_VideoStatus();
        $status->setPrivacyStatus($privacyStatus);

        return $status;
    }

    public function buildVideo(
        Google_Service_YouTube_VideoSnippet $snippet,
        Google_Service_YouTube_VideoStatus $status
    ): Google_Service_YouTube_Video {
        $video = new Google_Service_YouTube_Video();
        $video->setSnippet($snippet);
        $video->setStatus($status);

        return $video;
    }
}
