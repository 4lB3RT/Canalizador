<?php

declare(strict_types=1);

namespace Canalizador\Video\Application\UseCases;

final readonly class RetrieveVideoContentRequest
{
    public function __construct(
        public string $videoId,
    ) {
    }
}
