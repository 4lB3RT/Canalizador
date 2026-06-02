<?php

declare(strict_types=1);

namespace Helmreel\Shared\Profile\Application\UseCases\UpdateProfile;

use Helmreel\Shared\Profile\Domain\ProfileData;

final readonly class UpdateProfileResponse
{
    public function __construct(
        public ProfileData $profile,
    ) {
    }
}
