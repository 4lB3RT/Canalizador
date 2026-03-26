<?php

declare(strict_types=1);

namespace Canalizador\Shared\Infrastructure\ClientAPI;

use App\Services\GoogleClientService;
use Canalizador\Shared\Domain\Services\YouTubeAnalyticsServiceFactory;

final class YoutubeAnalyticsApiClient
{
    private ?\Google_Service_YouTubeAnalytics $analytics = null;

    public function __construct(
        private readonly GoogleClientService $googleClientService,
        private readonly YouTubeAnalyticsServiceFactory $youtubeAnalyticsServiceFactory
    ) {
    }

    public function getVideoMetrics(array $params): ?array
    {
        try {
            $client = $this->googleClientService->buildYouTubeAnalyticsClient();
            $this->analytics = $this->youtubeAnalyticsServiceFactory->create($client);

            $options = [];
            if (!empty($params['videoId'])) {
                $options['filters'] = 'video==' . $params['videoId'];
            }

            // MCP/Content Owner support
            if (!empty($params['contentOwnerId'])) {
                $ids = 'contentOwner==' . $params['contentOwnerId'];
            } else {
                $channelId = $params['channelId'] ?? 'MINE';
                $ids       = 'channel==' . $channelId;
            }

            $report = $this->analytics->reports->query(
                [
                    'ids'       => $ids,
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
