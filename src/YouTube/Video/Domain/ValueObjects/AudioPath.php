<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Domain\ValueObjects;

final readonly class AudioPath
{
    public function __construct(private string $value)
    {
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
