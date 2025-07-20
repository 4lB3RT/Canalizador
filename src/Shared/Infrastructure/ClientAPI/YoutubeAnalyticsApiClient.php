<?php

declare(strict_types = 1);

namespace Canalizador\Shared\Infrastructure\ClientAPI;

use App\Services\GoogleTokenService;
use Google_Client;

final class YoutubeAnalyticsApiClient
{
    public function __construct(private GoogleTokenService $tokenService, private Google_Client $client)
    {
    }

    public function getVideoMetrics(array $params): ?array
    {
        try {
            $this->client = new \Google_Client();
            $this->client->setScopes(['https://www.googleapis.com/auth/yt-analytics.readonly']);
            $this->client->setAccessType('offline');
            $accessToken = $this->tokenService->getAccessToken();
            if ($accessToken) {
                $this->client->setAccessToken($accessToken);
            }
            $this->analytics = new \Google_Service_YouTubeAnalytics($this->client);

            $options = [];
            if (!empty($params['videoId'])) {
                $options['filters'] = 'video==' . $params['videoId'];
            }
            $channelId = $params['channelId'] ?? 'MINE';
            $report    = $this->analytics->reports->query(
                [
                    'ids'       => 'channel==' . $channelId,
                    'startDate' => $params['startDate'],
                    'endDate'   => $params['endDate'],
                    'metrics'   => $params['metrics'],
                ] + $options
            );

            return json_decode(json_encode($report), true);
        } catch (\Throwable $e) {
            return [
                'error' => $e->getMessage(),
            ];
        }
    }
}
