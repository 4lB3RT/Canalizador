<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\News\Domain\Repositories;

use Canalizador\VideoProduction\News\Domain\Entities\News;

interface NewsRepository
{
    public function findLatest(): ?News;

    public function save(News $news): void;
}
