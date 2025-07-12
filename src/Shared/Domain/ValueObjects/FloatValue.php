<?php

declare(strict_types = 1);

namespace Src\Shared\Domain\ValueObjects;

abstract readonly class FloatValue
{
    public function __construct(private float $value)
    {
        if ($value < 0.0 || $value > 1.0) {
            throw new \InvalidArgumentException('Score must be between 0.0 and 1.0');
        }
    }

    public function value(): float
    {
        return $this->value;
    }
}
