<?php

declare(strict_types=1);

namespace Helmreel\Shared\Profile\Domain;

use Helmreel\Shared\Profile\Domain\Exceptions\EmailAlreadyTaken;
use Helmreel\Shared\Profile\Domain\Exceptions\ProfileNotFound;
use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;

interface ProfileRepository
{
    /**
     * @throws ProfileNotFound
     */
    public function findById(IntegerId $userId): ProfileData;

    /**
     * @throws ProfileNotFound
     */
    public function passwordHashOf(IntegerId $userId): string;

    public function emailExistsForOtherUser(string $email, IntegerId $userId): bool;

    /**
     * @throws ProfileNotFound
     * @throws EmailAlreadyTaken
     */
    public function update(
        IntegerId $userId,
        ?string $name,
        ?string $email,
        ?string $hashedPassword,
        ?string $avatarPath,
    ): ProfileData;
}
