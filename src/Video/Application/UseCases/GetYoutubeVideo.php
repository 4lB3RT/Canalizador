<?php

declare(strict_types = 1);

namespace Canalizador\Video\Application\UseCases;

use Canalizador\Recommendation\Domain\Entities\Recommendation;
use Canalizador\Video\Domain\Entities\Video;
use Canalizador\Video\Domain\Repositories\VideoRepository;
use Canalizador\Video\Domain\ValueObjects\VideoId;

final class GetYoutubeVideo
{
    public function __construct(private VideoRepository $videoRepository)
    {
    }

    public function get(VideoId $videoId): ?Video
    {
        $metrics = $this->videoRepository->getMetricsById($videoId);

        if (!$metrics) {
            return null;
        }

        $video = $this->videoRepository->findById($videoId);

        return $video;
    }
}
