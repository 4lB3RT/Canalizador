<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Domain\ValueObjects;

use Canalizador\Shared\Domain\ValueObjects\LocalPath;

final readonly class VideoToPublish
{
    public function __construct(
        public LocalPath $localPath,
        public string $title,
        public string $description,
    ) {
    }
}
