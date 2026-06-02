<?php

declare(strict_types=1);

namespace Helmreel\Shared\Header\Domain;

use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;

final readonly class UserHeaderData
{
    public function __construct(
        public IntegerId $id,
        public string $name,
        public string $email,
        public bool $googleLinked,
        public ?string $avatarPath = null,
    ) {
    }
}
