<?php

declare(strict_types=1);

namespace Canalizador\Video\Domain\ValueObjects;

use Canalizador\Shared\Domain\ValueObjects\StringValue;

final readonly class Description extends StringValue
{
    private const int MIN_LENGTH = 200;
    private const int MAX_LENGTH = 300;

    public function __construct(string $value)
    {
        parent::__construct($value);
        $this->validateLength($value);
    }

    public static function fromString(string $value): static
    {
        return new static($value);
    }

    private function validateLength(string $value): void
    {
        $length = mb_strlen($value);
        if ($length < self::MIN_LENGTH || $length > self::MAX_LENGTH) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Description must be between %d and %d characters, got %d',
                    self::MIN_LENGTH,
                    self::MAX_LENGTH,
                    $length
                )
            );
        }
    }
}
