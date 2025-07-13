<?php

declare(strict_types = 1);

namespace Canalizador\Shared\Domain\ValueObjects;

readonly class DateTime
{
    public function __construct(private \DateTimeImmutable $value)
    {
    }

    public function value(): \DateTimeImmutable
    {
        return $this->value;
    }
}
