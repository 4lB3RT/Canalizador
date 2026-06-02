<?php

declare(strict_types=1);

namespace Helmreel\YouTube\Video\Domain\ValueObjects;

use Helmreel\Shared\Shared\Domain\ValueObjects\LocalPath;

final readonly class VideoToPublish
{
    public function __construct(
        public LocalPath $localPath,
        public string $title,
        public string $description,
    ) {
    }
}
