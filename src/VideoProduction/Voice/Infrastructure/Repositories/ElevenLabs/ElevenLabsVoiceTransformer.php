<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Voice\Infrastructure\Repositories\ElevenLabs;

use Helmreel\Shared\Shared\Domain\Services\Clock;
use Helmreel\VideoProduction\Voice\Domain\Entities\Voice;
use Helmreel\VideoProduction\Voice\Domain\Entities\VoiceCollection;
use Helmreel\VideoProduction\Voice\Domain\ValueObjects\VoiceId;

final readonly class ElevenLabsVoiceTransformer
{
    public function __construct(
        private Clock $clock,
    ) {
    }

    /**
     * @param array<int, array<string, mixed>> $apiVoices
     */
    public function toCollection(array $apiVoices): VoiceCollection
    {
        $voices = [];

        foreach ($apiVoices as $apiVoice) {
            $voice = $this->toEntity($apiVoice);
            if ($voice !== null) {
                $voices[] = $voice;
            }
        }

        return new VoiceCollection($voices);
    }

    /**
     * @param array<string, mixed> $apiVoice
     */
    private function toEntity(array $apiVoice): ?Voice
    {
        $platformId = $apiVoice['voice_id'] ?? null;
        $name = $apiVoice['name'] ?? null;

        if (!is_string($platformId) || $platformId === '' || !is_string($name)) {
            return null;
        }

        return new Voice(
            id: VoiceId::fromString($platformId),
            userId: null,
            name: $name,
            sourceAudioPath: null,
            createdAt: $this->clock->now(),
            platformId: $platformId,
        );
    }
}
