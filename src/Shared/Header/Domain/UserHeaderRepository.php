<?php

declare(strict_types=1);

namespace Helmreel\Shared\Header\Domain;

use Helmreel\Shared\Header\Domain\Exceptions\UserHeaderNotFound;
use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;

interface UserHeaderRepository
{
    /**
     * @throws UserHeaderNotFound
     */
    public function findById(IntegerId $userId): UserHeaderData;
}
