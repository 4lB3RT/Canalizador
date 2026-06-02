<?php

declare(strict_types=1);

namespace Helmreel\Shared\Video\Domain\ValueObjects;

use Helmreel\Shared\Shared\Domain\ValueObjects\Description;
use Helmreel\Shared\Shared\Domain\ValueObjects\Title;

final readonly class VideoMetadata
{
    public function __construct(
        public Title       $title,
        public Description $description,
    ) {
    }
}
