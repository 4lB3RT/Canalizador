<?php

declare(strict_types=1);

namespace Canalizador\Video\Application\UseCases\CreateClip;

final readonly class CreateClipRequest
{
    public function __construct(
        public string $videoId,
    ) {
    }
}
