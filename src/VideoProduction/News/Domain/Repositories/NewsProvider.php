<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\News\Domain\Repositories;

use Canalizador\VideoProduction\News\Domain\Entities\News;

interface NewsProvider
{
    /**
     * @return News[]
     */
    public function fetch(): array;
}
