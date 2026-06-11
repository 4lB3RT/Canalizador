<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Voice\Application\UseCases\UpdateVoice;

final readonly class UpdateVoiceRequest
{
    public function __construct(
        public string $voiceId,
        public int $userId,
        public ?string $name = null,
        public ?float $stability = null,
        public ?float $similarityBoost = null,
        public ?float $style = null,
        public ?float $speed = null,
        public ?bool $useSpeakerBoost = null,
    ) {
    }

    public function hasSettings(): bool
    {
        return $this->stability !== null
            || $this->similarityBoost !== null
            || $this->style !== null
            || $this->speed !== null
            || $this->useSpeakerBoost !== null;
    }
}
