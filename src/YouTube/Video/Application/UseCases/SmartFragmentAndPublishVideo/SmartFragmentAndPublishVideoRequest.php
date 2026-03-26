<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Application\UseCases\SmartFragmentAndPublishVideo;

final readonly class SmartFragmentAndPublishVideoRequest
{
    public function __construct(
        public string $videoId,
        public string $localPath,
        public string $baseTitle,
        public string $baseDescription,
    ) {
    }
}
