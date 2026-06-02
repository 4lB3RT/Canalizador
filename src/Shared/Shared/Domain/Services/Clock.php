<?php

declare(strict_types=1);

namespace Helmreel\Shared\Shared\Domain\Services;

use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\DateTime;

interface Clock
{
    public function now(): DateTime;
}
