<?php

declare(strict_types=1);

namespace Canalizador\Weather\Domain\ValueObjects;

final readonly class Percentage
{
    public function __construct(private int $value)
    {
    }

    public static function fromInt(int $value): self
    {
        return new self($value);
    }

    public function value(): int
    {
        return $this->value;
    }
}
