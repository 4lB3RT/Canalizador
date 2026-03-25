<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Infrastructure\Services\YouTube;

use Google_Client;
use Google_Service_YouTube;
use Google_Service_YouTube_Video;

interface YouTubeVideoUploader
{
    public function upload(
        Google_Client $client,
        Google_Service_YouTube $service,
        Google_Service_YouTube_Video $video,
        string $videoPath,
        int $chunkSize
    ): string;
}
