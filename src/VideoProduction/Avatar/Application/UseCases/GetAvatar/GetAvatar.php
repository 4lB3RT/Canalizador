<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Avatar\Application\UseCases\GetAvatar;

use Helmreel\VideoProduction\Avatar\Domain\Exceptions\AvatarNotFound;
use Helmreel\VideoProduction\Avatar\Domain\Repositories\AvatarRepository;
use Helmreel\VideoProduction\Avatar\Domain\ValueObjects\AvatarId;

final readonly class GetAvatar
{
    public function __construct(
        private AvatarRepository $avatarRepository,
    ) {
    }

    /**
     * @throws AvatarNotFound
     */
    public function execute(string $avatarId, int $userId): array
    {
        $avatar = $this->avatarRepository->findById(AvatarId::fromString($avatarId));

        if ($avatar->userId()->value() !== $userId) {
            throw AvatarNotFound::withId($avatarId);
        }

        return $avatar->toArray();
    }
}
