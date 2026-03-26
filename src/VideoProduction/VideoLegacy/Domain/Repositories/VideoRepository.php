<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\VideoLegacy\Domain\Repositories;

use Canalizador\VideoProduction\VideoLegacy\Domain\Entities\Video;
use Canalizador\VideoProduction\VideoLegacy\Domain\ValueObjects\VideoId;
use Canalizador\YouTube\Metric\Domain\Entities\MetricCollection;

interface VideoRepository
{
    public function save(Video $video): void;

    public function findById(VideoId $videoId): ?Video;

    public function getMetricsById(VideoId $videoId): ?MetricCollection;
}
