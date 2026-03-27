<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Application\UseCases\GenerateShorts;

final readonly class GenerateShortsRequest
{
    public function __construct(
        public string $videoYoutubeId,
    ) {
    }
}
