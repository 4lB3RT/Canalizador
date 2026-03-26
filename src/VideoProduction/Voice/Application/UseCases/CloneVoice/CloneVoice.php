<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Voice\Application\UseCases\CloneVoice;

use Canalizador\Shared\Domain\Services\Clock;
use Canalizador\Shared\Domain\ValueObjects\LocalPath;
use Canalizador\VideoProduction\Voice\Domain\Entities\Voice;
use Canalizador\VideoProduction\Voice\Domain\Repositories\VoiceCloner;
use Canalizador\VideoProduction\Voice\Domain\Repositories\VoiceRepository;
use Canalizador\VideoProduction\Voice\Domain\ValueObjects\VoiceId;
use Illuminate\Support\Str;

final readonly class CloneVoice
{
    public function __construct(
        private VoiceCloner $voiceCloner,
        private VoiceRepository $voiceRepository,
        private Clock $clock,
    ) {
    }

    public function execute(string $audioPath, string $name): array
    {
        $platformId = $this->voiceCloner->clone($audioPath, $name);

        $voice = new Voice(
            id: new VoiceId(Str::uuid()->toString()),
            name: $name,
            sourceAudioPath: new LocalPath($audioPath),
            createdAt: $this->clock->now(),
            platformId: $platformId,
        );

        $this->voiceRepository->save($voice);

        return $voice->toArray();
    }
}
