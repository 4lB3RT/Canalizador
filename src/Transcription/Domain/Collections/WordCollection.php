<?php

declare(strict_types = 1);

namespace Canalizador\Metric\Domain\Entities;

use Canalizador\Shared\Domain\Collection;

final class MetricCollection extends Collection
{
    protected function type(): string
    {
        return Metric::class;
    }
}
