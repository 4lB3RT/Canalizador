<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Avatar\Domain\ValueObjects;

use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\StringValue;
use InvalidArgumentException;

final readonly class AvatarDescription extends StringValue
{
    private const int MAX_LENGTH = 2000;

    public function __construct(string $value)
    {
        $length = strlen($value);
        if ($length > self::MAX_LENGTH) {
            throw new InvalidArgumentException(
                sprintf('Avatar description cannot exceed %d characters. Current length: %d', self::MAX_LENGTH, $length)
            );
        }

        parent::__construct($value);
    }
}

