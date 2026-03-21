<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Avatar\Domain\ValueObjects;

use Canalizador\VideoProduction\Shared\Domain\ValueObjects\StringValue;
use InvalidArgumentException;

final readonly class AvatarName extends StringValue
{
    private const int MIN_LENGTH = 1;
    private const int MAX_LENGTH = 100;

    public function __construct(string $value)
    {
        $length = strlen(trim($value));
        if ($length < self::MIN_LENGTH) {
            throw new InvalidArgumentException('Avatar name cannot be empty');
        }
        if ($length > self::MAX_LENGTH) {
            throw new InvalidArgumentException(
                sprintf('Avatar name cannot exceed %d characters. Current length: %d', self::MAX_LENGTH, $length)
            );
        }

        parent::__construct(trim($value));
    }
}

