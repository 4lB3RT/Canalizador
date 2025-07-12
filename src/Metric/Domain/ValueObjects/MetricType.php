<?php

declare(strict_types = 1);

namespace Src\Metric\Domain\ValueObjects;

final readonly class MetricType
{
    public function __construct(private string $value)
    {
        $allowed = ['int', 'float', 'string'];
        if (!in_array($value, $allowed, true)) {
            throw new \InvalidArgumentException('Invalid metric type');
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
