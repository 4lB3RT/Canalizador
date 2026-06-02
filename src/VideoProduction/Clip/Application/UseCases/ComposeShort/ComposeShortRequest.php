<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Clip\Application\UseCases\ComposeShort;

final readonly class ComposeShortRequest
{
    public function __construct(
        public string $videoId,
    ) {
    }
}
