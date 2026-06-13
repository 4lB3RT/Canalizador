<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Video\Domain\ValueObjects;

use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\IntegerValue;
use InvalidArgumentException;

final readonly class TotalClips extends IntegerValue
{
    public const int MIN = 1;
    public const int MAX = 8;

    public function __construct(int $value)
    {
        if ($value < self::MIN || $value > self::MAX) {
            throw new InvalidArgumentException(
                sprintf('Total clips must be between %d and %d, %d given.', self::MIN, self::MAX, $value)
            );
        }

        parent::__construct($value);
    }
}
