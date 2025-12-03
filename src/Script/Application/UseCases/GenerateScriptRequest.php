<?php

declare(strict_types = 1);

namespace Canalizador\Script\Application\UseCases;

final readonly class GenerateScriptRequest
{
    public function __construct(
        public string $uuid,
        public ?string $prompt = null,
    ) {
    }
}
