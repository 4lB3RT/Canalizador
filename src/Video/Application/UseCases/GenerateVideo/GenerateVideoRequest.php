<?php

declare(strict_types=1);

namespace Canalizador\Video\Application\UseCases\GenerateVideo;

final readonly class GenerateVideoRequest
{
    public function __construct(
        public string $videoId,
        public string $scriptId,
        public string $channelId,
        public string $category,
        public ?string $avatarId = null,
        public ?string $prompt = null,
    ) {
    }
}
