<?php

declare(strict_types=1);

namespace Canalizador\Shared\Infrastructure\ClientAPI;

use Google_Client;
use Google_Service_YouTubeAnalytics;

class YoutubeAnalyticsApiClient
{
    private Google_Client $client;
    private Google_Service_YouTubeAnalytics $analytics;

    public function __construct(string $clientId, string $clientSecret, string $redirectUri, string $accessToken = null)
    {
        $this->client = new Google_Client();
        $this->client->setClientId($clientId);
        $this->client->setClientSecret($clientSecret);
        $this->client->setRedirectUri($redirectUri);
        $this->client->setScopes(['https://www.googleapis.com/auth/yt-analytics.readonly']);
        $this->client->setAccessType('offline');
        if ($accessToken) {
            $this->client->setAccessToken($accessToken);
        }
        $this->analytics = new Google_Service_YouTubeAnalytics($this->client);
    }

    public function getVideoMetrics(array $params): ?array
    {
        try {
            $options = [];
            if (!empty($params['videoId'])) {
                $options['filters'] = 'video==' . $params['videoId'];
            }
            $report = $this->analytics->reports->query([
                'channel' . $params['channelId'],
                $params['startDate'],
                $params['endDate'],
                $params['metrics'],
                $options
                ]
            );
            return $report->toSimpleObject();
        } catch (\Exception $e) {
            dd($e);
            // Optionally log the error
            return null;
        }
    }
}
