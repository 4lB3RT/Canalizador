<?php

declare(strict_types=1);

namespace Helmreel\Shared\Profile\Domain\Exceptions;

final class EmailAlreadyTaken extends \DomainException
{
    public static function withEmail(string $email): self
    {
        return new self("Email already taken: {$email}");
    }
}
