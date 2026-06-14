<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Voice\Domain\Entities;

use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\DateTime;
use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;
use Helmreel\Shared\Shared\Domain\ValueObjects\LocalPath;
use Helmreel\VideoProduction\Voice\Domain\ValueObjects\VoiceId;
use Helmreel\VideoProduction\Voice\Domain\ValueObjects\VoiceSettings;

final class Voice
{
    public function __construct(
        private readonly VoiceId $id,
        private readonly ?IntegerId $userId,
        private string $name,
        private readonly ?LocalPath $sourceAudioPath,
        private readonly DateTime $createdAt,
        private readonly ?string $platformId = null,
        private readonly ?LocalPath $convertedAudioPath = null,
        private VoiceSettings $settings = new VoiceSettings(),
    ) {
    }

    public function id(): VoiceId
    {
        return $this->id;
    }

    public function userId(): ?IntegerId
    {
        return $this->userId;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function settings(): VoiceSettings
    {
        return $this->settings;
    }

    public function updateName(string $name): void
    {
        $this->name = $name;
    }

    public function updateSettings(VoiceSettings $settings): void
    {
        $this->settings = $settings;
    }

    public function sourceAudioPath(): ?LocalPath
    {
        return $this->sourceAudioPath;
    }

    public function convertedAudioPath(): ?LocalPath
    {
        return $this->convertedAudioPath;
    }

    public function platformId(): ?string
    {
        return $this->platformId;
    }

    public function createdAt(): DateTime
    {
        return $this->createdAt;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id->value(),
            'user_id' => $this->userId?->value(),
            'name' => $this->name,
            'platform_id' => $this->platformId,
            'settings' => $this->settings->toArray(),
            'created_at' => $this->createdAt->value()->format('Y-m-d H:i:s'),
        ];
    }
}
