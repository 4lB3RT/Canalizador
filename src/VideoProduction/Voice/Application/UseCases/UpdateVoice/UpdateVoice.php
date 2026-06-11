<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Voice\Application\UseCases\UpdateVoice;

use Helmreel\VideoProduction\Voice\Domain\Exceptions\VoiceNotFound;
use Helmreel\VideoProduction\Voice\Domain\Repositories\VoiceRepository;
use Helmreel\VideoProduction\Voice\Domain\ValueObjects\VoiceId;
use Helmreel\VideoProduction\Voice\Domain\ValueObjects\VoiceSettings;

final readonly class UpdateVoice
{
    public function __construct(
        private VoiceRepository $voiceRepository,
    ) {
    }

    /**
     * @throws VoiceNotFound
     */
    public function execute(UpdateVoiceRequest $request): array
    {
        $voice = $this->voiceRepository->findById(VoiceId::fromString($request->voiceId));

        if ($voice === null || $voice->userId()->value() !== $request->userId) {
            throw VoiceNotFound::withId($request->voiceId);
        }

        if ($request->name !== null) {
            $voice->updateName($request->name);
        }

        if ($request->hasSettings()) {
            $current = $voice->settings();
            $voice->updateSettings(new VoiceSettings(
                stability: $request->stability ?? $current->stability,
                similarityBoost: $request->similarityBoost ?? $current->similarityBoost,
                style: $request->style ?? $current->style,
                speed: $request->speed ?? $current->speed,
                useSpeakerBoost: $request->useSpeakerBoost ?? $current->useSpeakerBoost,
            ));
        }

        $this->voiceRepository->save($voice);

        return $voice->toArray();
    }
}
