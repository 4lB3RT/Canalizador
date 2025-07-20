<?php

declare(strict_types = 1);

namespace Canalizador\Metric\Domain\ValueObjects;

final readonly class MetricType
{
    public function __construct(private string $value)
    {
        $allowed = ['int', 'INTEGER', 'float', 'string'];
        if (!in_array($value, $allowed, true)) {
            throw new \InvalidArgumentException('Invalid metric type');
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
