<?php

declare(strict_types = 1);

namespace Canalizador\Video\Application\UseCases;

use Canalizador\Video\Domain\Entities\Video;
use Canalizador\Video\Domain\Repositories\VideoRepository;
use Canalizador\Video\Domain\ValueObjects\VideoId;

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
