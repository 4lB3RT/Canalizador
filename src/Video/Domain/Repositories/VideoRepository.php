<?php

declare(strict_types = 1);

namespace Src\Video\Domain\Repositories;

use Src\Video\Domain\Entities\Video;
use Src\Video\Domain\ValueObjects\VideoId;

interface VideoRepository
{
    public function findById(VideoId $videoId): ?Video;
}
