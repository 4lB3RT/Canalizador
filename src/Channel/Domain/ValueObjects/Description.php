<?php

declare(strict_types=1);

namespace Canalizador\Channel\Domain\ValueObjects;

use Canalizador\Shared\Domain\ValueObjects\StringValue;
use InvalidArgumentException;

final readonly class Description extends StringValue
{
    private const int MAX_LENGTH = 1000;

    public function __construct(string $value)
    {
        if (strlen($value) > self::MAX_LENGTH) {
            throw new InvalidArgumentException(
                sprintf('Description cannot exceed %d characters. Current length: %d', self::MAX_LENGTH, strlen($value))
            );
        }

        parent::__construct($value);
    }
}

