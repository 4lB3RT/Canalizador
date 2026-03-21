<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Video\Application\UseCases\CreateVideo;

use Canalizador\VideoProduction\Video\Domain\Entities\Video;

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
