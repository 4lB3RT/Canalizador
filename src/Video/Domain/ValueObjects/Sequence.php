<?php

declare(strict_types=1);

namespace Canalizador\Video\Domain\ValueObjects;

use Canalizador\Shared\Domain\ValueObjects\IntegerValue;

final readonly class Sequence extends IntegerValue
{
    public function __construct(int $value)
    {
        if ($value < 1 || $value > 5) {
            throw new \InvalidArgumentException('Sequence must be between 1 and 5');
        }

        parent::__construct($value);
    }
}
