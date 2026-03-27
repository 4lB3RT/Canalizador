<?php

declare(strict_types=1);

namespace Canalizador\Shared\Shared\Domain\ValueObjects\Essentials;

abstract readonly class StringValue
{
    public function __construct(private string $value)
    {
    }

    public static function fromString(string $value): static
    {
        return new static($value);
    }

    public function value(): string
    {
        return $this->value;
    }
}
