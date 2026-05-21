<?php

declare(strict_types=1);

namespace Canalizador\Shared\Header\Domain;

use Canalizador\Shared\Header\Domain\Exceptions\UserHeaderNotFound;
use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;

interface UserHeaderRepository
{
    /**
     * @throws UserHeaderNotFound
     */
    public function findById(IntegerId $userId): UserHeaderData;
}
