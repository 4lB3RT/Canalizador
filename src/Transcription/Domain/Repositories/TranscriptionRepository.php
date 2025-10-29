<?php

declare(strict_types = 1);

namespace Canalizador\Transcription\Domain\Repositories;

use Canalizador\Transcription\Domain\Entities\Transcription;
use Canalizador\Video\Domain\ValueObjects\VideoId;

interface TranscriptionRepository
{
    public function findByVideoId(VideoId $videoId): ?Transcription;
}
