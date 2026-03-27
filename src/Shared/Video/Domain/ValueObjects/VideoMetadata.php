<?php

declare(strict_types=1);

namespace Canalizador\Shared\Video\Domain\ValueObjects;

use Canalizador\Shared\Shared\Domain\ValueObjects\Description;
use Canalizador\Shared\Shared\Domain\ValueObjects\Title;

final readonly class VideoMetadata
{
    public function __construct(
        public Title       $title,
        public Description $description,
    ) {
    }
}
