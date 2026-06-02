<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\News\Domain\Entities;

use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\DateTime;
use Helmreel\VideoProduction\News\Domain\ValueObjects\Description;
use Helmreel\VideoProduction\News\Domain\ValueObjects\NewsId;
use Helmreel\VideoProduction\News\Domain\ValueObjects\PublishedAt;
use Helmreel\VideoProduction\News\Domain\ValueObjects\Title;

final class News
{
    public function __construct(
        private readonly NewsId $id,
        private readonly Title $title,
        private readonly Description $description,
        private readonly PublishedAt $publishedAt,
        private readonly DateTime $createdAt,
    ) {
    }

    public function id(): NewsId
    {
        return $this->id;
    }

    public function title(): Title
    {
        return $this->title;
    }

    public function description(): Description
    {
        return $this->description;
    }

    public function publishedAt(): PublishedAt
    {
        return $this->publishedAt;
    }

    public function createdAt(): DateTime
    {
        return $this->createdAt;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id->value(),
            'title' => $this->title->value(),
            'description' => $this->description->value(),
            'published_at' => $this->publishedAt->value()->format('Y-m-d H:i:s'),
            'created_at' => $this->createdAt->value()->format('Y-m-d H:i:s'),
        ];
    }
}
