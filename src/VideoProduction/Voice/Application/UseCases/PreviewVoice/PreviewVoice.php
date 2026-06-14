<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Voice\Application\UseCases\PreviewVoice;

use Helmreel\VideoProduction\Voice\Domain\Repositories\VoiceRepository;
use Helmreel\VideoProduction\Voice\Domain\ValueObjects\VoiceSettings;

final readonly class PreviewVoice
{
    public function __construct(
        private VoiceRepository $voiceRepository,
    ) {
    }

    public function execute(string $platformId, string $text): string
    {
        return $this->voiceRepository->generateSpeech($text, $platformId, new VoiceSettings());
    }
}
