<?php

declare(strict_types=1);

namespace Canalizador\Video\Application\UseCases\CreateVideo;

use Canalizador\Video\Domain\Entities\Video;

final readonly class CreateVideoResponse
{
    public function __construct(
        public Video $video,
    ) {
    }

    public function toArray(): array
    {
        return $this->video->toArray();
    }
}
