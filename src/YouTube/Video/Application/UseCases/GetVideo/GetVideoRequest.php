<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Application\UseCases\GetVideo;

final readonly class GetVideoRequest
{
    public function __construct(
        public string $videoId,
    ) {
    }
}
