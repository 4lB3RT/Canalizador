<?php

declare(strict_types=1);

namespace Canalizador\Channel\Domain\ValueObjects;

final readonly class ChannelBrand
{
    public function __construct(private ?string $value)
    {
        if ($value !== null && trim($value) === '') {
            throw new \InvalidArgumentException('ChannelBrand cannot be empty string');
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

