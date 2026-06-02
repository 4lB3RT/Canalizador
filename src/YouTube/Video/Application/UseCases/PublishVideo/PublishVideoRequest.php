<?php

declare(strict_types=1);

namespace Helmreel\YouTube\Video\Application\UseCases\PublishVideo;

final readonly class PublishVideoRequest
{
    public function __construct(
        public string $videoId,
        public string $platform
    ) {
    }
}
