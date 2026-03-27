<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\News\Infrastructure\Repositories\Eloquent;

use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\DateTime;
use Canalizador\VideoProduction\News\Domain\Entities\News;
use Canalizador\VideoProduction\News\Domain\Repositories\NewsRepository;
use Canalizador\VideoProduction\News\Domain\ValueObjects\Description;
use Canalizador\VideoProduction\News\Domain\ValueObjects\NewsId;
use Canalizador\VideoProduction\News\Domain\ValueObjects\PublishedAt;
use Canalizador\VideoProduction\News\Domain\ValueObjects\Title;
use Canalizador\VideoProduction\News\Infrastructure\DAO\NewsDAO;

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
