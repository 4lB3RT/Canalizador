<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Avatar\Domain\Repositories;

use Canalizador\VideoProduction\Avatar\Domain\Entities\Avatar;
use Canalizador\VideoProduction\Avatar\Domain\Exceptions\AvatarNotFound;
use Canalizador\VideoProduction\Avatar\Domain\ValueObjects\AvatarId;
use Canalizador\VideoProduction\Shared\Domain\ValueObjects\IntegerId;

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

