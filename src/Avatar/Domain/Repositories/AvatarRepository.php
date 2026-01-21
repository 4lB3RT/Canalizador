<?php

declare(strict_types=1);

namespace Canalizador\Avatar\Domain\Repositories;

use Canalizador\Avatar\Domain\Entities\Avatar;
use Canalizador\Avatar\Domain\Exceptions\AvatarNotFound;
use Canalizador\Avatar\Domain\ValueObjects\AvatarId;
use Canalizador\Shared\Domain\ValueObjects\IntegerId;

interface AvatarRepository
{
    public function save(Avatar $avatar): void;

    /**
     * @throws AvatarNotFound
     */
    public function findById(AvatarId $id): Avatar;

    /**
     * @return Avatar[]
     */
    public function findByUserId(IntegerId $userId): array;

    public function delete(AvatarId $id): void;
}

