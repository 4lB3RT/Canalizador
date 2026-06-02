<?php

declare(strict_types=1);

namespace Helmreel\YouTube\Video\Domain\Repositories;

use Helmreel\Shared\Shared\Domain\ValueObjects\LocalPath;
use Helmreel\YouTube\Video\Domain\ValueObjects\AudioPath;

interface AudioExtractor
{
    public function extract(LocalPath $videoPath): AudioPath;
}
