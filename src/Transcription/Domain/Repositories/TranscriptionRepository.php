<?php

declare(strict_types = 1);

namespace Canalizador\Transcription\Domain\Repositories;

use Canalizador\Metric\Domain\Entities\MetricCollection;
use Canalizador\Transcription\Domain\Entities\Transcription;
use Canalizador\Transcription\Domain\ValueObjects\TranscriptionId;
use Canalizador\Video\Domain\Entities\Video;
use Canalizador\Video\Domain\ValueObjects\VideoId;

interface TranscriptionRepository
{
    public function findById(TranscriptionId $videoId): ?Transcription;
}
