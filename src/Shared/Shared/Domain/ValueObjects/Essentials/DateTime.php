<?php

declare(strict_types=1);

namespace Canalizador\Shared\Shared\Domain\ValueObjects\Essentials;

use DateTimeImmutable;

readonly class DateTime
{
    public function __construct(private DateTimeImmutable $value)
    {
    }

    public function value(): DateTimeImmutable
    {
        return $this->value;
    }

    public static function now(): self
    {
        return new self(new DateTimeImmutable());
    }


}
