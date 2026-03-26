<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Metric\Domain\ValueObjects;

use InvalidArgumentException;

final readonly class MetricType
{
    public function __construct(private string $value)
    {
        $allowed = ['int', 'INTEGER', 'FLOAT', 'float', 'string'];
        if (!in_array($value, $allowed, true)) {
            throw new InvalidArgumentException('Invalid metric type');
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
