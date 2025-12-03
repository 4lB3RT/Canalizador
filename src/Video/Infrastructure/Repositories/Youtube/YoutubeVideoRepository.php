<?php

declare(strict_types = 1);

namespace Canalizador\Video\Infrastructure\Repositories\Youtube;

use Canalizador\Metric\Domain\Entities\Metric;
use Canalizador\Metric\Domain\Entities\MetricCollection;
use Canalizador\Metric\Domain\ValueObjects\MetricName;
use Canalizador\Metric\Domain\ValueObjects\MetricType;
use Canalizador\Metric\Domain\ValueObjects\MetricValue;
use Canalizador\Shared\Domain\ValueObjects\DateTime;
use Canalizador\Shared\Infrastructure\ClientAPI\YoutubeAnalyticsApiClient;
use Canalizador\Shared\Infrastructure\ClientAPI\YoutubeDataApiClient;
use Canalizador\Video\Domain\Entities\Video;
use Canalizador\Video\Domain\Exceptions\VideoNotFound;
use Canalizador\Video\Domain\Repositories\VideoRepository;
use Canalizador\Video\Domain\ValueObjects\Category;
use Canalizador\Video\Domain\ValueObjects\VideoId;
use Canalizador\Video\Infrastructure\DataTransformers\VideoDataTransformer;
use DateTimeImmutable;
use Throwable;

final readonly class YoutubeVideoRepository implements VideoRepository
{
    public function __construct(
        private YoutubeDataApiClient      $youtubeClient,
        private ?YoutubeAnalyticsApiClient $youtubeAnalyticsClient = null,
    ) {
    }

    /**
     * @throws \DateMalformedStringException
     * @throws VideoNotFound
     * @throws Throwable
     */
    public function findById(VideoId $videoId): ?Video
    {
        $data = $this->youtubeClient->getVideoById($videoId->value());

        if (!$data) {
            throw VideoNotFound::default();
        }

        $publishedAt = new DateTime(new DateTimeImmutable($data['snippet']['publishedAt']) ?? '');

        return VideoDataTransformer::fromArray([
            'id'               => $videoId->value(),
            'title'            => $data['snippet']['title'],
            'published_at'     => $publishedAt->value()->format('Y-m-d H:i:s'),
            'category'         => Category::VIDEO->value,
            'metrics'          => [],
            'url'              => 'https://www.youtube.com/watch?v=' . $videoId->value(),
            'video_local_path' => null,
            'audio_local_path' => null,
            'transcription'    => null,
        ]);
    }

    public function getMetricsById(VideoId $videoId): ?MetricCollection
    {
        $params = [
            'channelId' => 'UCXModX2oqGBqVjf4M6cFmrw',
            'videoId'   => $videoId->value(),
            'startDate' => date('Y-m-d', strtotime('-30 days')),
            'endDate'   => date('Y-m-d'),
            'metrics'   => 'views,estimatedMinutesWatched,averageViewDuration,averageViewPercentage,subscribersGained,subscribersLost,likes,dislikes,shares,comments,annotationClickThroughRate,annotationCloseRate,cardClickRate,cardTeaserClickRate',
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

    public function save(Video $video): void
    {
        throw new \Exception('Not implemented');
    }
}
