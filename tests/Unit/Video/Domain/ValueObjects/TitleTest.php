<?php

declare(strict_types = 1);

namespace Tests\Unit\Video\Domain\ValueObjects;

use PHPUnit\Framework\TestCase;
use Src\Video\Domain\ValueObjects\Title;

final class TitleTest extends TestCase
{
    public function testItCreatesTitleWithNonEmptyValue(): void
    {
        $title = new Title('My Video Title');
        $this->assertSame('My Video Title', $title->value());
    }

    public function testItThrowsExceptionForEmptyValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Title('');
    }

    public function testItThrowsExceptionForWhitespaceValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Title('   ');
    }
}
