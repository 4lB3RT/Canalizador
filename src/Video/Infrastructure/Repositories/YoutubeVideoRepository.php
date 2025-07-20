<?php

declare(strict_types = 1);

namespace Canalizador\Video\Infrastructure\Repositories;

use Canalizador\Category\Domain\Entities\Category;
use Canalizador\Category\Domain\ValueObjects\CategoryName;
use Canalizador\Metric\Domain\Entities\Metric;
use Canalizador\Metric\Domain\Entities\MetricCollection;
use Canalizador\Metric\Domain\ValueObjects\MetricName;
use Canalizador\Metric\Domain\ValueObjects\MetricType;
use Canalizador\Metric\Domain\ValueObjects\MetricValue;
use Canalizador\Shared\Domain\ValueObjects\DateTime;
use Canalizador\Shared\Infrastructure\ClientAPI\YoutubeAnalyticsApiClient;
use Canalizador\Shared\Infrastructure\ClientAPI\YoutubeDataApiClient;
use Canalizador\Video\Domain\Entities\Video;
use Canalizador\Video\Domain\Repositories\VideoRepository;
use Canalizador\Video\Domain\ValueObjects\Title;
use Canalizador\Video\Domain\ValueObjects\VideoId;
use DateTimeImmutable;
use Throwable;

final class YoutubeVideoRepository implements VideoRepository
{
    public function __construct(
        private YoutubeDataApiClient $youtubeClient,
        private YoutubeAnalyticsApiClient $youtubeAnalyticsClient,
    ) {
    }

    public function findById(VideoId $videoId): ?Video
    {
        $data = $this->youtubeClient->getVideoById($videoId->value());

        if (!$data) {
            return null;
        }
        $snippet     = $data['snippet'] ?? [];
        $title       = new Title($snippet['title'] ?? '');
        $publishedAt = new DateTime(new DateTimeImmutable($snippet['publishedAt']) ?? '');

        $metrics = new MetricCollection([]);

        return new Video($videoId, $title, $publishedAt, $metrics, new Category(new CategoryName('technology')));
    }

    public function getMetricsById(VideoId $videoId): ?MetricCollection
    {
        $params = [
            'channelId' => 'UCXModX2oqGBqVjf4M6cFmrw',
            'videoId'   => $videoId->value(),
            'startDate' => date('Y-m-d', strtotime('-30 days')),
            'endDate'   => date('Y-m-d'),
            'metrics'   => 'comments',
            'filters'   => 'video==' . $videoId->value(),
        ];

        try {
            $report  = $this->youtubeAnalyticsClient->getVideoMetrics($params);
            $metrics = [];
            if (isset($report['rows'][0]) && isset($report['columnHeaders'])) {
                foreach ($report['columnHeaders'] as $i => $header) {
                    $metrics[] = new Metric(
                        new MetricName($header['name']),
                        new MetricType($header['dataType']),
                        new MetricValue((string) $report['rows'][0][$i])
                    );
                }
            }

            return new MetricCollection($metrics);
        } catch (Throwable $e) {
            return null;
        }
    }
}
