<?php

declare(strict_types = 1);

namespace Src\Metric\Domain\ValueObjects;

final readonly class MetricValue
{
    public function __construct(private int|float|string $value)
    {
    }

    public function value(): int|float|string
    {
        return $this->value;
    }
}
