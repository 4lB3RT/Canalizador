<?php

declare(strict_types=1);

namespace Canalizador\Clip\Application\UseCases\ComposeShort;

final readonly class ComposeShortRequest
{
    public function __construct(
        public string $videoId,
    ) {
    }
}
