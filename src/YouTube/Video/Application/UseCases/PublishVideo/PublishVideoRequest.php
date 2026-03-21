<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Application\UseCases\PublishVideo;

final readonly class PublishVideoRequest
{
    public function __construct(
        public string $videoId,
        public string $platform
    ) {
    }
}
