<?php

declare(strict_types=1);

namespace Canalizador\Script\Domain\Repositories;

interface ScriptGenerator
{
    public function generate(?string $prompt = null): string;
}
