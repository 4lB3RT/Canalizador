<?php

declare(strict_types = 1);

namespace Canalizador\Script\Application\UseCases;

use Canalizador\Script\Domain\Entities\Script;

final readonly class GenerateScriptResponse
{
    public function __construct(
        public Script $script,
    ) {
    }

    public function toArray(): array
    {
        return $this->script->toArray();
    }
}
