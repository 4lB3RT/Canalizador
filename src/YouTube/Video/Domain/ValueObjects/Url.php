<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Domain\ValueObjects;

final readonly class Url
{
    private const string BASE = 'https://www.youtube.com/watch?v=';

    public function __construct(private string $value)
    {
    }

    public static function fromId(Id $id): self
    {
        return new self(self::BASE . $id->value());
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
