<?php

declare(strict_types = 1);

namespace Src\Shared\Domain\ValueObjects;

abstract readonly class StringValue
{
    public function __construct(private string $value)
    {
        if (trim($value) === '') {
            throw new \InvalidArgumentException('String value cannot be empty');
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
