<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Application\UseCases\GenerateShorts;

final readonly class GenerateShortsResponse
{
    /** @param string[] $publishedShortIds */
    public function __construct(
        public array $publishedShortIds,
    ) {
    }

    public function toArray(): array
    {
        return [
            'published_short_ids' => $this->publishedShortIds,
        ];
    }
}
