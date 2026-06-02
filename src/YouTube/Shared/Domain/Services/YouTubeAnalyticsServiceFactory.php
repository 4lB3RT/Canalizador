<?php

declare(strict_types=1);

namespace Helmreel\YouTube\Shared\Domain\Services;

use Google_Client;
use Google_Service_YouTubeAnalytics;

interface YouTubeAnalyticsServiceFactory
{
    public function create(Google_Client $client): Google_Service_YouTubeAnalytics;
}
