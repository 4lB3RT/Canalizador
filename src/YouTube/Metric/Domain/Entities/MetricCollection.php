<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Metric\Domain\Entities;

use Canalizador\Shared\Shared\Domain\Collection;

final class MetricCollection extends Collection
{
    public static function fromArray(array $metrics): self
    {
        return new self($metrics);
    }

    protected function type(): string
    {
        return Metric::class;
    }
}
