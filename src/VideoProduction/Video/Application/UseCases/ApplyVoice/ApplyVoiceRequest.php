<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Video\Application\UseCases\ApplyVoice;

final readonly class ApplyVoiceRequest
{
    public function __construct(
        public string $videoId,
        public string $avatarId,
    ) {
    }
}
