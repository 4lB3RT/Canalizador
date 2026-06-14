<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Voice\Domain\Entities;

use Helmreel\Shared\Shared\Domain\Collection;

final class VoiceCollection extends Collection
{
    protected function type(): string
    {
        return Voice::class;
    }
}
