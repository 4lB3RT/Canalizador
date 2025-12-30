<?php

declare(strict_types=1);

namespace Canalizador\Shared\Domain\ValueObjects;

abstract readonly class IntegerValue
{
    public function __construct(private int $value)
    {
        if (!is_int($value)) {
            throw new \InvalidArgumentException('IntegerValue must be an integer');
        }

        if ($value < 0) {
            throw new \InvalidArgumentException('IntegerValue cannot be negative');
        }
    }

    public static function fromInt(int $value): static
    {
        return new static($value);
    }

    public function value(): int
    {
        return $this->value;
    }
}
