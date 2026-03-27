<?php

declare(strict_types=1);

namespace Canalizador\Shared\Shared\Domain\Services;

use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\DateTime;

interface Clock
{
    public function now(): DateTime;
}
