<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\VideoLegacy\Domain\Repositories;

use Canalizador\Metric\Domain\Entities\MetricCollection;
use Canalizador\VideoProduction\VideoLegacy\Domain\Entities\Video;
use Canalizador\VideoProduction\VideoLegacy\Domain\ValueObjects\VideoId;

interface VideoRepository
{
    public function save(Video $video): void;

    public function findById(VideoId $videoId): ?Video;

    public function getMetricsById(VideoId $videoId): ?MetricCollection;
}
