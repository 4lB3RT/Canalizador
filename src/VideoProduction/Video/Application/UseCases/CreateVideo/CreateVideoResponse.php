<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Video\Application\UseCases\CreateVideo;

use Helmreel\VideoProduction\Video\Domain\Entities\Video;

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
