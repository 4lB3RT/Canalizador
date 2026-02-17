<?php

declare(strict_types=1);

namespace Canalizador\News\Domain\Repositories;

use Canalizador\News\Domain\Entities\News;

interface NewsRepository
{
    public function findLatest(): ?News;

    public function save(News $news): void;
}
