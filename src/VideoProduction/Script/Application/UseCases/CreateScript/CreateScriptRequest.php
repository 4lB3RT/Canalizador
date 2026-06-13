<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Script\Application\UseCases\CreateScript;

final readonly class CreateScriptRequest
{
    public function __construct(
        public string $scriptId,
        public int $userId,
        public string $category,
        public int $totalClips = 5,
        public ?string $title = null,
        public string $language = 'es',
    ) {
    }
}
