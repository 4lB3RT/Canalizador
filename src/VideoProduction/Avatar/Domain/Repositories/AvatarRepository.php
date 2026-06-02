<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Avatar\Domain\Repositories;

use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;
use Helmreel\VideoProduction\Avatar\Domain\Entities\Avatar;
use Helmreel\VideoProduction\Avatar\Domain\Exceptions\AvatarNotFound;
use Helmreel\VideoProduction\Avatar\Domain\ValueObjects\AvatarId;

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

