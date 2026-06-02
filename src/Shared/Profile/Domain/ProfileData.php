<?php

declare(strict_types=1);

namespace Helmreel\Shared\Profile\Domain;

use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;

final readonly class ProfileData
{
    public function __construct(
        public IntegerId $id,
        public string $name,
        public string $email,
        public ?string $avatarPath,
    ) {
    }
}
