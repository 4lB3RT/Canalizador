<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Shared\Domain\ValueObjects;

abstract readonly class FloatValue
{
    public function __construct(private float $value)
    {
    }

    public static function fromFloat(float $value): self
    {
        return new static($value);
    }

    public function value(): float
    {
        return $this->value;
    }
}
