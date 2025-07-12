<?php

declare(strict_types = 1);

namespace Tests\Unit\Video\Domain\ValueObjects;

use PHPUnit\Framework\TestCase;
use Src\Video\Domain\ValueObjects\Views;

final class ViewsTest extends TestCase
{
    public function testItCreatesViewsWithPositiveValue(): void
    {
        $views = new Views(100);
        $this->assertSame(100, $views->value());
    }

    public function testItThrowsExceptionForNegativeValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Views(-1);
    }
}
