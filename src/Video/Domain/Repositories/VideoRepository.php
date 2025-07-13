<?php

declare(strict_types = 1);

namespace Canalizador\Video\Domain\Repositories;

use Canalizador\Metric\Domain\Entities\MetricCollection;
use Canalizador\Video\Domain\Entities\Video;
use Canalizador\Video\Domain\ValueObjects\VideoId;

interface VideoRepository
{
    public function findById(VideoId $videoId): ?Video;

    public function getMetricsById(VideoId $videoId): ?MetricCollection;
}
