<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Voice\Application\UseCases\GenerateVoice;

use Helmreel\VideoProduction\Voice\Domain\Exceptions\VoiceBlocked;
use Helmreel\VideoProduction\Voice\Domain\Exceptions\VoiceNotFound;
use Helmreel\VideoProduction\Voice\Domain\Repositories\VoiceRepository;
use Helmreel\VideoProduction\Voice\Domain\ValueObjects\VoiceId;
use RuntimeException;

final readonly class GenerateVoice
{
    public function __construct(
        private VoiceRepository $voiceRepository,
    ) {
    }

    /**
     * @throws VoiceNotFound
     */
    public function execute(string $voiceId, string $text, int $userId): string
    {
        $voice = $this->voiceRepository->findById(VoiceId::fromString($voiceId));

        if ($voice === null || $voice->userId()?->value() !== $userId) {
            throw VoiceNotFound::withId($voiceId);
        }

        $platformId = $voice->platformId();
        if ($platformId === null) {
            throw new RuntimeException("Voice '{$voiceId}' has no platform id; it cannot synthesize speech.");
        }

        try {
            return $this->voiceRepository->generateSpeech($text, $platformId, $voice->settings());
        } catch (VoiceBlocked $e) {
            $this->voiceRepository->delete($voice->id());

            throw $e;
        }
    }
}
