<?php

declare(strict_types = 1);

namespace Helmreel\YouTube\Video\Domain\Entities;

use Helmreel\Shared\Shared\Domain\Collection;

final class VideoCollection extends Collection
{
    protected function type(): string
    {
        return Video::class;
    }
}
