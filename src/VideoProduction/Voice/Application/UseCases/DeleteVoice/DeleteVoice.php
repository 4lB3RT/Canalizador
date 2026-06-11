<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Voice\Application\UseCases\DeleteVoice;

use Helmreel\VideoProduction\Voice\Domain\Exceptions\VoiceNotFound;
use Helmreel\VideoProduction\Voice\Domain\Repositories\VoiceRepository;
use Helmreel\VideoProduction\Voice\Domain\ValueObjects\VoiceId;
use Illuminate\Support\Facades\File;

final readonly class DeleteVoice
{
    public function __construct(
        private VoiceRepository $voiceRepository,
    ) {
    }

    /**
     * @throws VoiceNotFound
     */
    public function execute(string $voiceId, int $userId): void
    {
        $id = VoiceId::fromString($voiceId);
        $voice = $this->voiceRepository->findById($id);

        if ($voice === null || $voice->userId()->value() !== $userId) {
            throw VoiceNotFound::withId($voiceId);
        }

        $sourcePath = $voice->sourceAudioPath()->value();
        if ($sourcePath !== '' && File::exists($sourcePath)) {
            File::delete($sourcePath);
        }

        $convertedPath = $voice->convertedAudioPath()?->value();
        if ($convertedPath !== null && $convertedPath !== '' && File::exists($convertedPath)) {
            File::delete($convertedPath);
        }

        $this->voiceRepository->delete($id);
    }
}
