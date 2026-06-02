<?php

declare(strict_types=1);

namespace Helmreel\Shared\Shared\Domain\ValueObjects;

use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\IntegerValue;

final readonly class PerPage extends IntegerValue
{
    public const MIN = 1;
    public const MAX = 100;

    public function __construct(int $value)
    {
        if ($value < self::MIN) {
            throw new \InvalidArgumentException(
                sprintf('PerPage must be greater than or equal to %d', self::MIN)
            );
        }

        if ($value > self::MAX) {
            throw new \InvalidArgumentException(
                sprintf('PerPage must be less than or equal to %d', self::MAX)
            );
        }

        parent::__construct($value);
    }
}
