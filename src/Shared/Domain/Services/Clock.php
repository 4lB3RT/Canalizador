<?php

declare(strict_types=1);

namespace Canalizador\Shared\Domain\Services;

use Canalizador\Shared\Domain\ValueObjects\DateTime;

interface Clock
{
    public function now(): DateTime;
}
