<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Weather\Domain\ValueObjects;

final readonly class Temperature
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
