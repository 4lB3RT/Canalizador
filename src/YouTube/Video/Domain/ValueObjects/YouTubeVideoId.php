<?php

declare(strict_types=1);

namespace Helmreel\YouTube\Video\Domain\ValueObjects;

final readonly class YouTubeVideoId
{
    public function __construct(
        private string $value
    ) {
    }

    public function value(): string
    {
        return $this->value;
    }
}
