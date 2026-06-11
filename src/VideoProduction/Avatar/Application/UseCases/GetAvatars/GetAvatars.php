<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Avatar\Application\UseCases\GetAvatars;

use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;
use Helmreel\VideoProduction\Avatar\Domain\Repositories\AvatarRepository;

final readonly class GetAvatars
{
    public function __construct(
        private AvatarRepository $avatarRepository,
    ) {
    }

    public function execute(int $userId): array
    {
        $avatars = $this->avatarRepository->findByUserId(new IntegerId($userId));

        return array_map(fn ($avatar) => $avatar->toArray(), $avatars);
    }
}
