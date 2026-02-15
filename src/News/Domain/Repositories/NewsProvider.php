<?php

declare(strict_types=1);

namespace Canalizador\News\Domain\Repositories;

use Canalizador\News\Domain\Entities\News;

interface NewsProvider
{
    /**
     * @return News[]
     */
    public function fetch(): array;
}
