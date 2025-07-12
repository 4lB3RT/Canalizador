<?php

declare(strict_types = 1);

namespace Src\Video\Domain\Contracts;

use Src\Video\Domain\Entities\Video;
use Src\Video\Domain\ValueObjects\VideoId;

interface VideoRepository
{
    public function find(VideoId $id): ?Video;

    public function save(Video $video): void;
}
