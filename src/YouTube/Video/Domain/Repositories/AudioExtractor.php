<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Domain\Repositories;

use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\LocalPath;
use Canalizador\YouTube\Video\Domain\ValueObjects\AudioPath;

interface AudioExtractor
{
    public function extract(LocalPath $videoPath): AudioPath;
}
