<?php

declare(strict_types=1);

namespace Canalizador\Clip\Application\UseCases\GenerateClip;

final readonly class GenerateClipRequest
{
    public function __construct(
        public string $clipId,
    ) {
    }
}
