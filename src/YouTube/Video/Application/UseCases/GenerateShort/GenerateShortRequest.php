<?php

declare(strict_types = 1);

namespace Canalizador\YouTube\Video\Application\UseCases\GenerateShort;

final readonly class GenerateShortRequest
{
    public function __construct(
        public string $videoId,
    ) {
    }
}
