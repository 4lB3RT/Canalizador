<?php

declare(strict_types=1);

namespace Canalizador\Shared\Domain\Exceptions;

use Canalizador\Shared\Domain\Exceptions\Essentials\BadRequest;

final class InvalidCollectionType extends BadRequest
{
    private const string MESSAGE = 'The collection type is invalid.';

    public static function create(): self
    {
        return new self(self::MESSAGE);
    }
}
