<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Metric\Domain\ValueObjects;

use InvalidArgumentException;

final readonly class MetricName
{
    public function __construct(private string $value)
    {
        if (trim($value) === '') {
            throw new InvalidArgumentException('Metric name cannot be empty');
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
