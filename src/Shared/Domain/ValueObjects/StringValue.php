<?php

declare(strict_types = 1);

namespace Canalizador\Shared\Domain\ValueObjects;

abstract readonly class StringValue
{
    public function __construct(private string $value)
    {
        if (trim($value) === '') {
            throw new \InvalidArgumentException('String value cannot be empty');
        }
    }

    public static function fromString(string $value): self
    {
        return new static($value);
    }

    public function value(): string
    {
        return $this->value;
    }
}
