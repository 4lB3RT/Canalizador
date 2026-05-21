<?php

declare(strict_types=1);

namespace Canalizador\Shared\Shared\Domain\ValueObjects;

final readonly class Search
{
    public const MAX_LENGTH = 200;

    public function __construct(private string $value)
    {
        $trimmed = trim($value);

        if ($trimmed === '') {
            throw new \InvalidArgumentException('Search cannot be empty');
        }

        if (mb_strlen($trimmed) > self::MAX_LENGTH) {
            throw new \InvalidArgumentException(
                sprintf('Search cannot exceed %d characters', self::MAX_LENGTH)
            );
        }
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function value(): string
    {
        return trim($this->value);
    }
}
