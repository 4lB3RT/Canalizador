<?php

declare(strict_types = 1);

namespace Canalizador\YouTube\Transcription\Domain\Repositories;

use Canalizador\Video\Domain\Entities\Video;
use Canalizador\Video\Domain\Exceptions\VideoLocalPathNotFound;
use Canalizador\Video\Domain\ValueObjects\VideoId;
use Canalizador\YouTube\Transcription\Domain\Entities\Transcription;

interface TranscriptionRepository
{
    /* @throws VideoLocalPathNotFound */
    public function findByVideo(Video $video): ?Transcription;
}
