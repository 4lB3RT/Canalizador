<?php

declare(strict_types = 1);

namespace Src\Video\Infrastructure\Repositories;

use Src\Metric\Domain\Entities\MetricCollection;
use Src\Shared\Domain\ValueObjects\Category;
use Src\Shared\Domain\ValueObjects\DateTime;
use Src\Shared\Infrastructure\ClientAPI\YoutubeDataApiClient;
use Src\Video\Domain\Entities\Video;
use Src\Video\Domain\Repositories\VideoRepository;
use Src\Video\Domain\ValueObjects\Title;
use Src\Video\Domain\ValueObjects\VideoId;

final class YoutubeVideoRepository implements VideoRepository
{
    public function __construct(private YoutubeDataApiClient $youtubeClient)
    {
    }

    public function findById(VideoId $videoId): ?Video
    {
        $data = $this->youtubeClient->getVideoById($videoId->value());
        if (!$data) {
            return null;
        }
        $snippet     = $data['snippet'] ?? [];
        $title       = new Title($snippet['title'] ?? '');
        $publishedAt = new DateTime($snippet['publishedAt'] ?? '');
        $category    = new Category($snippet['categoryId'] ?? '');

        $metrics = new MetricCollection([]);

        return new Video($videoId, $title, $publishedAt, $metrics, $category);
    }
}
