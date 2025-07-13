<?php

declare(strict_types = 1);

namespace Canalizador\Category\Domain\Entities;

use Canalizador\Category\Domain\ValueObjects\CategoryName;
use Canalizador\Shared\Domain\ValueObjects\StringValue;

final readonly class Category
{
    public function __construct(
        private CategoryName $name
    ) {
    }

    public function name(): StringValue
    {
        return $this->name;
    }
}
