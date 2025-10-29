<?php

declare(strict_types = 1);

namespace Canalizador\Recommendation\Domain\ValueObjects;

use Canalizador\Shared\Domain\Collection;

final class ValueCollection extends Collection
{
    protected function type(): string
    {
        return Value::class;
    }
}
