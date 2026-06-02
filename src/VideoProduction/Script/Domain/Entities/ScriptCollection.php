<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Script\Domain\Entities;

use Helmreel\Shared\Shared\Domain\Collection;

final class ScriptCollection extends Collection
{
    protected function type(): string
    {
        return Script::class;
    }
}
