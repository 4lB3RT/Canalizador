<?php

declare(strict_types = 1);

namespace Canalizador\Video\Domain\Entities;

use Canalizador\Category\Domain\Entities\Category;
use Canalizador\Metric\Domain\Entities\MetricCollection;
use Canalizador\Shared\Domain\ValueObjects\DateTime;
use Canalizador\Video\Domain\ValueObjects\Title;
use Canalizador\Video\Domain\ValueObjects\VideoId;

final class Video
{
    public function __construct(
        private readonly VideoId $id,
        private readonly Title $title,
        private readonly DateTime $publishedAt,
        private readonly MetricCollection $metrics,
        private readonly Category $category
    ) {
    }

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
