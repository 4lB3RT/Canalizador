<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Video\Domain\Entities;

use Canalizador\Shared\Domain\Collection;

final class VideoCollection extends Collection
{
    protected function type(): string
    {
        return Video::class;
    }
}
