<?php

declare(strict_types = 1);

namespace Tests\Unit\Video\Domain\Entities;

use PHPUnit\Framework\TestCase;
use Src\Metric\Domain\Entities\MetricCollection;
use Src\Shared\Domain\ValueObjects\Category;
use Src\Shared\Domain\ValueObjects\DateTime;
use Src\Shared\Domain\ValueObjects\StringValue;
use Src\Video\Domain\Entities\Video;
use Src\Video\Domain\ValueObjects\Title;
use Src\Video\Domain\ValueObjects\VideoId;

final class VideoTest extends TestCase
{
    public function testItCreatesVideoWithAllProperties(): void
    {
        $videoId     = new VideoId('abc123');
        $title       = new Title('Test Video');
        $publishedAt = new DateTime(new \DateTimeImmutable('2024-01-01T12:00:00Z'));
        $metrics     = new MetricCollection([]);
        $category    = new Category(new StringValue('Education'));

        $video = new Video($videoId, $title, $publishedAt, $metrics, $category);

        $this->assertSame($videoId, $video->id());
        $this->assertSame($title, $video->title());
        $this->assertSame($publishedAt, $video->publishedAt());
        $this->assertSame($metrics, $video->metrics());
        $this->assertSame($category, $video->category());
    }

    public function testItThrowsExceptionForEmptyCategoryName(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $videoId     = new VideoId('abc123');
        $title       = new Title('Test Video');
        $publishedAt = new DateTime(new \DateTimeImmutable('2024-01-01T12:00:00Z'));
        $metrics     = new MetricCollection([]);
        $category    = new Category(new StringValue(''));
        new Video($videoId, $title, $publishedAt, $metrics, $category);
    }

    public function testItThrowsExceptionForEmptyTitle(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $videoId     = new VideoId('abc123');
        $title       = new Title('');
        $publishedAt = new DateTime(new \DateTimeImmutable('2024-01-01T12:00:00Z'));
        $metrics     = new MetricCollection([]);
        $category    = new Category(new StringValue('Education'));
        new Video($videoId, $title, $publishedAt, $metrics, $category);
    }

    public function testItThrowsExceptionForEmptyVideoId(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $videoId     = new VideoId('');
        $title       = new Title('Test Video');
        $publishedAt = new DateTime(new \DateTimeImmutable('2024-01-01T12:00:00Z'));
        $metrics     = new MetricCollection([]);
        $category    = new Category(new StringValue('Education'));

        new Video(
            id: $videoId,
            title: $title,
            publishedAt: $publishedAt,
            metrics: $metrics,
            category: $category
        );
    }

    public function testItReturnsCategoryNameValue(): void
    {
        $videoId     = new VideoId('abc123');
        $title       = new Title('Test Video');
        $publishedAt = new DateTime(new \DateTimeImmutable('2024-01-01T12:00:00Z'));
        $metrics     = new MetricCollection([]);
        $category    = new Category(new StringValue('Education'));
        $video       = new Video($videoId, $title, $publishedAt, $metrics, $category);
        $this->assertSame('Education', $video->category()->name()->value());
    }

    public function testItReturnsPublishedAtValue(): void
    {
        $videoId     = new VideoId('abc123');
        $title       = new Title('Test Video');
        $publishedAt = new DateTime(new \DateTimeImmutable('2024-01-01T12:00:00Z'));
        $metrics     = new MetricCollection([]);
        $category    = new Category(new StringValue('Education'));
        $video       = new Video(
            $videoId,
            $title,
            $publishedAt,
            $metrics,
            $category
        );

        $this->assertEquals(
            new \DateTimeImmutable('2024-01-01T12:00:00Z'),
            $video->publishedAt()->value()
        );
    }

    public function testItReturnsIdentifiers(): void
    {
        $videoId     = new VideoId('abc123');
        $title       = new Title('Test Video');
        $publishedAt = new DateTime(new \DateTimeImmutable('2024-01-01T12:00:00Z'));
        $metrics     = new MetricCollection([]);
        $category    = new Category(new StringValue('Education'));
        $video       = new Video(
            $videoId,
            $title,
            $publishedAt,
            $metrics,
            $category
        );

        $this->assertSame('abc123', $video->id()->value());
        $this->assertSame('Test Video', $video->title()->value());
        $this->assertEquals(
            new \DateTimeImmutable('2024-01-01T12:00:00Z'),
            $video->publishedAt()->value()
        );
        $this->assertSame('Education', $video->category()->name()->value());
    }
}
