<?php

declare(strict_types = 1);

namespace Tests\Unit\Video\Domain\ValueObjects;

use PHPUnit\Framework\TestCase;
use Src\Video\Domain\ValueObjects\Duration;

final class DurationTest extends TestCase
{
    public function testItCreatesDurationWithPositiveValue(): void
    {
        $duration = new Duration(120);
        $this->assertSame(120, $duration->seconds());
    }

    public function testItThrowsExceptionForNegativeValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Duration(-10);
    }
}
