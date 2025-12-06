<?php

declare(strict_types=1);

namespace Canalizador\Video\Application\UseCases;

final readonly class GenerateVideoFromScriptResponse
{
    public function __construct(
        public string $generationId
    ) {
    }

    public function toArray(): array
    {
        return [
            'generation_id' => $this->generationId,
        ];
    }
}
