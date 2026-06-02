<?php

declare(strict_types=1);

namespace Helmreel\YouTube\Shared\Infrastructure\Services;

use Helmreel\YouTube\Shared\Domain\Services\YouTubeAnalyticsServiceFactory;
use Google_Client;
use Google_Service_YouTubeAnalytics;

final class GoogleYouTubeAnalyticsServiceFactory implements YouTubeAnalyticsServiceFactory
{
    public function create(Google_Client $client): Google_Service_YouTubeAnalytics
    {
        return new Google_Service_YouTubeAnalytics($client);
    }
}
