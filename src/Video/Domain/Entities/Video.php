<?php

declare(strict_types=1);

namespace Src\Video\Domain\Entities;

use Src\Metric\Domain\Entities\MetricCollection;
use Src\Video\Domain\ValueObjects\VideoId;
use Src\Video\Domain\ValueObjects\Title;
use Src\Shared\Domain\ValueObjects\DateTime;
use Src\Shared\Domain\ValueObjects\Category;

final class Video
{
    public function __construct(
        private readonly VideoId $id,
        private readonly Title $title,
        private readonly DateTime $publishedAt,
        private readonly MetricCollection $metrics,
        private readonly Category $category
    ) {}

    public function id(): VideoId
    {
        return $this->id;
    }

    public function title(): Title
    {
        return $this->title;
    }

    public function publishedAt(): DateTime
    {
        return $this->publishedAt;
    }

    public function metrics(): MetricCollection
    {
        return $this->metrics;
    }

    public function category(): Category
    {
        return $this->category;
    }
}
