<?php

declare(strict_types=1);

namespace Canalizador\Video\Domain\ValueObjects;

use Canalizador\Shared\Domain\ValueObjects\StringValue;

final readonly class Description extends StringValue
{
    public function __construct(string $value)
    {
        parent::__construct($value);
    }

    public static function fromString(string $value): static
    {
        return new static($value);
    }
}
