<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Video\Application\UseCases\CreateVideo;

final readonly class CreateVideoRequest
{
    public function __construct(
        public string $videoId,
        public int $userId,
        public string $scriptId,
        public string $category,
        public ?string $avatarId = null,
        public string $resolution = '720p',
        public int $totalClips = 5,
        public string $language = 'es',
    ) {
    }
}
