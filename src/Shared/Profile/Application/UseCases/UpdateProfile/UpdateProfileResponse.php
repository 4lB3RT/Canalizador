<?php

declare(strict_types=1);

namespace Canalizador\Shared\Profile\Application\UseCases\UpdateProfile;

use Canalizador\Shared\Profile\Domain\ProfileData;

final readonly class UpdateProfileResponse
{
    public function __construct(
        public ProfileData $profile,
    ) {
    }
}
