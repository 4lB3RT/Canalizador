<?php

declare(strict_types=1);

namespace Src\Shared\Domain\ValueObjects;

final readonly class IntegerId
{
    public function __construct(private int $value)
    {
        if ($value < 0) {
            throw new \InvalidArgumentException('Id cannot be negative');
        }
    }

    public function value(): int
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}

