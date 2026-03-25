<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\VideoLegacy\Application\UseCases;

use Canalizador\VideoProduction\VideoLegacy\Domain\Entities\Video;
use Canalizador\VideoProduction\VideoLegacy\Domain\Repositories\VideoRepository;
use Canalizador\VideoProduction\VideoLegacy\Domain\ValueObjects\VideoId;

final readonly class GetYoutubeVideo
{
    public function __construct(private VideoRepository $externalVideoRepository)
    {
    }

    public function get(VideoId $videoId): ?Video
    {
        $metrics = $this->externalVideoRepository->getMetricsById($videoId);

        if (!$metrics) {
            return null;
        }

        $video = $this->externalVideoRepository->findById($videoId);

        $video->updateMetrics($metrics);

        return $video;
    }
}
