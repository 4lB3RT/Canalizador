<?php

declare(strict_types=1);

namespace Canalizador\News\Infrastructure\Repositories\Eloquent;

use Canalizador\News\Domain\Entities\News;
use Canalizador\News\Domain\Repositories\NewsRepository;
use Canalizador\News\Infrastructure\DAO\NewsDAO;

final class EloquentNewsRepository implements NewsRepository
{
    public function save(News $news): void
    {
        NewsDAO::updateOrCreate(
            ['news_id' => $news->id()->value()],
            [
                'title' => $news->title()->value(),
                'description' => $news->description()->value(),
                'published_at' => $news->publishedAt()->value(),
                'created_at' => $news->createdAt()->value(),
            ]
        );
    }
}
