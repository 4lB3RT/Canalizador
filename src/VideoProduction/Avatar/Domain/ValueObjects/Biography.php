<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Avatar\Domain\ValueObjects;

use Canalizador\VideoProduction\Shared\Domain\ValueObjects\StringValue;
use InvalidArgumentException;

final readonly class Biography extends StringValue
{

    public function __construct(string $value)
    {
        parent::__construct($value);
    }
}

