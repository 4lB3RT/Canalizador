<?php

declare(strict_types=1);

namespace Canalizador\Avatar\Application\UseCases\CreateAvatar;

final readonly class CreateAvatarRequest
{
    public function __construct(
        public string $avatarId,
        public int $userId,
        public string $name,
        public string $profileImagePath,
        public string $biography,
        public string $presentationStyle,
    ) {
    }
}

