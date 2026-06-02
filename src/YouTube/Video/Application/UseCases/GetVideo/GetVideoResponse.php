<?php

declare(strict_types=1);

namespace Helmreel\YouTube\Video\Application\UseCases\GetVideo;

use Helmreel\YouTube\Video\Domain\Entities\Video;

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
