<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Domain\ValueObjects;

final readonly class Id
{
    public function __construct(private string $value)
    {
    }

    public static function generate(): self
    {
        return new self(\Illuminate\Support\Str::uuid()->toString());
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }
}
