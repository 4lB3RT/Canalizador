<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\News\Domain\Repositories;

use Helmreel\VideoProduction\News\Domain\Entities\News;

interface NewsProvider
{
    /**
     * @return News[]
     */
    public function fetch(): array;
}
