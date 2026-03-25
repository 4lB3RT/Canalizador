<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Application\UseCases\GetVideo;

use Canalizador\YouTube\Video\Domain\Entities\Video;

final readonly class GetVideoResponse
{
    public function __construct(
        private Video $video,
    ) {
    }

    public function toArray(): array
    {
        return $this->video->toArray();
    }
}
