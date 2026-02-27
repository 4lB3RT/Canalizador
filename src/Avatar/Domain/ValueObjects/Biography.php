<?php

declare(strict_types=1);

namespace Canalizador\Avatar\Domain\ValueObjects;

use Canalizador\Shared\Domain\ValueObjects\StringValue;
use InvalidArgumentException;

final readonly class Biography extends StringValue
{

    public function __construct(string $value)
    {
        parent::__construct($value);
    }
}

