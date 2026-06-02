<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\VideoLegacy\Domain\Repositories;

use Helmreel\VideoProduction\VideoLegacy\Domain\Entities\Video;
use Helmreel\VideoProduction\VideoLegacy\Domain\ValueObjects\VideoId;
use Helmreel\YouTube\Metric\Domain\Entities\MetricCollection;

interface VideoRepository
{
    public function save(Video $video): void;

    public function findById(VideoId $videoId): ?Video;

    public function getMetricsById(VideoId $videoId): ?MetricCollection;
}
