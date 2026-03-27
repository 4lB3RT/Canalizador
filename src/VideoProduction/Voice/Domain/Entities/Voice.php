<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Voice\Domain\Entities;

use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\DateTime;
use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\LocalPath;
use Canalizador\VideoProduction\Voice\Domain\ValueObjects\VoiceId;

final class Voice
{
    public function __construct(
        private readonly VoiceId $id,
        private readonly string $name,
        private readonly LocalPath $sourceAudioPath,
        private readonly DateTime $createdAt,
        private readonly ?string $platformId = null,
        private readonly ?LocalPath $convertedAudioPath = null,
    ) {
    }

    public function id(): VoiceId
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function sourceAudioPath(): LocalPath
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
            'name' => $this->name,
            'source_audio_path' => $this->sourceAudioPath->value(),
            'converted_audio_path' => $this->convertedAudioPath?->value(),
            'platform_id' => $this->platformId,
            'created_at' => $this->createdAt->value()->format('Y-m-d H:i:s'),
        ];
    }
}
