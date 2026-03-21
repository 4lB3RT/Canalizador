<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Domain\ValueObjects;

use DateTimeImmutable;

final readonly class PublishedAt
{
    public function __construct(private DateTimeImmutable $value)
    {
    }

    public static function fromString(string $value): self
    {
        return new self(new DateTimeImmutable($value));
    }

    public static function fromDateTimeImmutable(DateTimeImmutable $value): self
    {
        return new self($value);
    }

    public function value(): DateTimeImmutable
    {
        return $this->value;
    }

    public function format(string $format): string
    {
        return $this->value->format($format);
    }
}
