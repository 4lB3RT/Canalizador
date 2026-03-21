<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Video\Application\UseCases\RetrieveVideoContent;

final readonly class RetrieveVideoContentRequest
{
    public function __construct(
        public string $videoId,
    ) {
    }
}
