<?php

declare(strict_types=1);

namespace Canalizador\Script\Domain\Repositories;

interface ScriptIdeaGenerator
{
    public function generateIdea(): string;
}
