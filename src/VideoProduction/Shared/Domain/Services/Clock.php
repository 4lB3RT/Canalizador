<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Shared\Domain\Services;

use Canalizador\VideoProduction\Shared\Domain\ValueObjects\DateTime;

interface Clock
{
    public function now(): DateTime;
}
