<?php

declare(strict_types=1);

namespace Helmreel\Shared\Profile\Application\UseCases\UpdateProfile;

use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;

final readonly class UpdateProfileRequest
{
    public function __construct(
        public IntegerId $userId,
        public ?string $name = null,
        public ?string $email = null,
        public ?string $plainPassword = null,
        public ?string $currentPassword = null,
        public ?string $avatarPath = null,
    ) {
    }
}
