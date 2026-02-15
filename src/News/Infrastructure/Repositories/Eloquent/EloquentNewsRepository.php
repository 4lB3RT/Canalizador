<?php

declare(strict_types=1);

namespace Canalizador\News\Infrastructure\Repositories\Eloquent;

use Canalizador\News\Domain\Entities\News;
use Canalizador\News\Domain\Repositories\NewsRepository;
use Canalizador\News\Domain\ValueObjects\Description;
use Canalizador\News\Domain\ValueObjects\NewsId;
use Canalizador\News\Domain\ValueObjects\PublishedAt;
use Canalizador\News\Domain\ValueObjects\Title;
use Canalizador\News\Infrastructure\DAO\NewsDAO;
use Canalizador\Shared\Domain\ValueObjects\DateTime;

final class EloquentNewsRepository implements NewsRepository
{
    public function findLatest(): ?News
    {
        $dao = NewsDAO::query()->orderByDesc('published_at')->first();

        if ($dao === null) {
            return null;
        }

        return new News(
            id: new NewsId($dao->news_id),
            title: new Title($dao->title),
            description: new Description($dao->description),
            publishedAt: new PublishedAt($dao->published_at->toDateTimeImmutable()),
            createdAt: new DateTime($dao->created_at->toDateTimeImmutable()),
        );
    }

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
