<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\News\Domain\Repositories;

use Helmreel\VideoProduction\News\Domain\Entities\News;

interface NewsRepository
{
    public function findLatest(): ?News;

    public function save(News $news): void;
}
