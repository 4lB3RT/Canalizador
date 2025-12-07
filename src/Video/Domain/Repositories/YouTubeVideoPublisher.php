<?php

declare(strict_types=1);

namespace Canalizador\Video\Domain\Repositories;

use Canalizador\Video\Domain\Exceptions\VideoGenerationFailed;

interface YouTubeVideoPublisher
{
    /**
     * Publishes a video to YouTube.
     *
     * @param string $videoPath Local path to the video file
     * @param string $title Video title
     * @param string $description Video description
     * @param array<string> $tags Video tags
     * @param string $privacyStatus Privacy status: 'private', 'unlisted', or 'public'
     * @return string The YouTube video ID
     * @throws VideoGenerationFailed
     */
    public function publish(
        string $videoPath,
        string $title,
        string $description,
        array $tags = [],
        string $privacyStatus = 'private'
    ): string;
}
