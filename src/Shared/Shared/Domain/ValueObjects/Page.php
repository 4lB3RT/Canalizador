<?php

declare(strict_types=1);

namespace Helmreel\Shared\Shared\Domain\ValueObjects;

use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\IntegerValue;

final readonly class Page extends IntegerValue
{
    public function __construct(int $value)
    {
        if ($value < 1) {
            throw new \InvalidArgumentException('Page must be greater than or equal to 1');
        }

        parent::__construct($value);
    }
}
