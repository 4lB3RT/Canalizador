<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Avatar\Application\UseCases\UpdateAvatar;

final readonly class UpdateAvatarRequest
{
    public function __construct(
        public string $avatarId,
        public int $userId,
        public ?string $name = null,
        public ?string $category = null,
        public ?string $presentationStyle = null,
        public ?string $biography = null,
        public ?string $description = null,
        public ?string $voiceId = null,
        public ?string $voicePlatformId = null,
        public ?string $voiceCatalogName = null,
        public ?array $voiceSettings = null,
        public bool $clearVoice = false,
        public ?string $profileImagePath = null,
    ) {
    }
}
