<?php

declare(strict_types = 1);

namespace Helmreel\YouTube\Transcription\Domain\Repositories;

use Helmreel\Video\Domain\Entities\Video;
use Helmreel\Video\Domain\Exceptions\VideoLocalPathNotFound;
use Helmreel\Video\Domain\ValueObjects\VideoId;
use Helmreel\YouTube\Transcription\Domain\Entities\Transcription;

interface TranscriptionRepository
{
    /* @throws VideoLocalPathNotFound */
    public function findByVideo(Video $video): ?Transcription;
}
