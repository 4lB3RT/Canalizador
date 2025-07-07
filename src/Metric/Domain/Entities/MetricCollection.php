<?php

declare(strict_types=1);

namespace Src\Metric\Domain\Entities;

use Src\Shared\Domain\Collection;

/**
 * @extends Collection<Metric>
 */
final class MetricCollection extends Collection
{
    protected function type(): string
    {
        return Metric::class;
    }
}
