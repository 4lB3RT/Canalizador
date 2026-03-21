<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Application\UseCases\FragmentAndPublishVideo;

final readonly class FragmentAndPublishVideoRequest
{
    public function __construct(
        public string $localPath,
        public string $baseTitle,
        public string $baseDescription,
        public int $segmentDurationSeconds = 60,
    ) {
    }
}
