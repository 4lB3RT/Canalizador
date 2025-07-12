<?php

declare(strict_types = 1);

namespace Src\Metric\Domain\ValueObjects;

final readonly class MetricName
{
    public function __construct(private string $value)
    {
        if (trim($value) === '') {
            throw new \InvalidArgumentException('Metric name cannot be empty');
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
