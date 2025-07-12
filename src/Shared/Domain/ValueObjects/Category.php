<?php

declare(strict_types = 1);

namespace Src\Shared\Domain\ValueObjects;

final readonly class Category
{
    public function __construct(
        private StringValue $name
    ) {
        if (trim($name->value()) === '') {
            throw new \InvalidArgumentException('Category name cannot be empty');
        }
    }

    public function name(): StringValue
    {
        return $this->name;
    }
}
