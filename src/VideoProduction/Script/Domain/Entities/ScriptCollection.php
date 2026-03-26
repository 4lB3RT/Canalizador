<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Script\Domain\Entities;

use Canalizador\Shared\Domain\Collection;

final class ScriptCollection extends Collection
{
    protected function type(): string
    {
        return Script::class;
    }
}
