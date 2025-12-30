<?php

declare(strict_types=1);

namespace Canalizador\Video\Infrastructure\Services\YouTube;

use Canalizador\Video\Domain\Services\YouTubeServiceFactory;
use Google_Client;
use Google_Service_YouTube;

final class GoogleYouTubeServiceFactory implements YouTubeServiceFactory
{
    public function create(Google_Client $client): Google_Service_YouTube
    {
        return new Google_Service_YouTube($client);
    }
}
