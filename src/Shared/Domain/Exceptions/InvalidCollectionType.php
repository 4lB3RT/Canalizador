<?php

declare(strict_types = 1);

namespace Canalizador\Shared\Domain\Exceptions;

use VendingMachine\Shared\Domain\Errors\Essentials\BadRequest;

final class InvalidCollectionType extends BadRequest
{
    private const string MESSAGE = 'The collection type is invalid.';

    public static function create(): self
    {
        return new self(self::MESSAGE);
    }
}
