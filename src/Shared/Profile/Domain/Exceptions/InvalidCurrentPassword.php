<?php

declare(strict_types=1);

namespace Helmreel\Shared\Profile\Domain\Exceptions;

final class InvalidCurrentPassword extends \DomainException
{
    public static function create(): self
    {
        return new self('Current password is invalid');
    }
}
