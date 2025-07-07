<?php

declare(strict_types=1);

namespace Tests\Unit\Video\Domain\ValueObjects;

use PHPUnit\Framework\TestCase;
use Src\Video\Domain\ValueObjects\VideoId;

final class VideoIdTest extends TestCase
{
    public function testItCreatesVideoIdWithNonEmptyValue(): void
    {
        $id = new VideoId('abc123');
        $this->assertSame('abc123', $id->value());
    }

    public function testItThrowsExceptionForEmptyValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new VideoId('');
    }
}

