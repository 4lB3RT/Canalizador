<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Channel\Domain\ValueObjects;

final readonly class CustomUrl
{
    public function __construct(private ?string $value)
    {
        if ($value !== null && trim($value) === '') {
            throw new \InvalidArgumentException('CustomUrl cannot be empty string');
        }
    }

    public static function fromString(?string $value): self
    {
        return new self($value);
    }

    public function value(): ?string
    {
        return $this->value;
    }
}

