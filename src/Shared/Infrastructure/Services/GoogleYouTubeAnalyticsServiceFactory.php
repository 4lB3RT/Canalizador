<?php

declare(strict_types=1);

namespace Canalizador\Shared\Infrastructure\Services;

use Canalizador\Shared\Domain\Services\YouTubeAnalyticsServiceFactory;
use Google_Client;
use Google_Service_YouTubeAnalytics;

final class GoogleYouTubeAnalyticsServiceFactory implements YouTubeAnalyticsServiceFactory
{
    public function create(Google_Client $client): Google_Service_YouTubeAnalytics
    {
        return new Google_Service_YouTubeAnalytics($client);
    }
}
