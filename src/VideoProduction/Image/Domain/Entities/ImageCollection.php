<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Image\Domain\Entities;

use Helmreel\Shared\Shared\Domain\Collection;

final class ImageCollection extends Collection
{
    protected function type(): string
    {
        return Image::class;
    }
}
