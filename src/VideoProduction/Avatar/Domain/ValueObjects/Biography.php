<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Avatar\Domain\ValueObjects;

use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\StringValue;

final readonly class Biography extends StringValue
{

    public function __construct(string $value)
    {
        parent::__construct($value);
    }
}

