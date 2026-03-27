<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Avatar\Domain\ValueObjects;

use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\StringValue;

final readonly class Biography extends StringValue
{

    public function __construct(string $value)
    {
        parent::__construct($value);
    }
}

