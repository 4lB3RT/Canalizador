<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Video\Application\UseCases\CreateVideo;

final readonly class CreateVideoRequest
{
    public function __construct(
        public string $videoId,
        public string $scriptId,
        public string $channelId,
        public string $category,
        public ?string $avatarId = null,
    ) {
    }
}
