<?php

declare(strict_types=1);

namespace Canalizador\VideoLegacy\Domain\Repositories;

use Canalizador\Metric\Domain\Entities\MetricCollection;
use Canalizador\VideoLegacy\Domain\Entities\Video;
use Canalizador\VideoLegacy\Domain\ValueObjects\VideoId;

interface VideoRepository
{
    public function save(Video $video): void;

    public function findById(VideoId $videoId): ?Video;

    public function getMetricsById(VideoId $videoId): ?MetricCollection;
}
