<?php

declare(strict_types=1);

namespace Canalizador\Video\Domain\ValueObjects;

final readonly class VideoMetadata
{
    public function __construct(
        public Title $title,
        public Description $description,
    ) {
    }
}
