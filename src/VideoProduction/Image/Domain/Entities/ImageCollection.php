<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Image\Domain\Entities;

use Canalizador\Shared\Domain\Collection;

final class ImageCollection extends Collection
{
    protected function type(): string
    {
        return Image::class;
    }
}
