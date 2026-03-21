<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Clip\Application\UseCases\CreateClip;

final readonly class CreateClipRequest
{
    public function __construct(
        public string $videoId,
    ) {
    }
}
