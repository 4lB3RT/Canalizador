<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Avatar\Application\UseCases\AutoCreateAvatar;

final readonly class AutoCreateAvatarRequest
{
    public function __construct(
        public string $avatarId,
        public int $userId,
    ) {
    }
}
