<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Voice\Domain\ValueObjects;

use InvalidArgumentException;

final readonly class VoiceSettings
{
    public function __construct(
        public float $stability = 0.5,
        public float $similarityBoost = 0.75,
        public float $style = 0.0,
        public float $speed = 1.0,
        public bool $useSpeakerBoost = true,
    ) {
        $this->assertRange('stability', $stability, 0.0, 1.0);
        $this->assertRange('similarity_boost', $similarityBoost, 0.0, 1.0);
        $this->assertRange('style', $style, 0.0, 1.0);
        $this->assertRange('speed', $speed, 0.5, 2.0);
    }

    public function toArray(): array
    {
        return [
            'stability' => $this->stability,
            'similarity_boost' => $this->similarityBoost,
            'style' => $this->style,
            'speed' => $this->speed,
            'use_speaker_boost' => $this->useSpeakerBoost,
        ];
    }

    private function assertRange(string $name, float $value, float $min, float $max): void
    {
        if ($value < $min || $value > $max) {
            throw new InvalidArgumentException(
                sprintf('%s must be between %s and %s. Given: %s', $name, $min, $max, $value)
            );
        }
    }
}
