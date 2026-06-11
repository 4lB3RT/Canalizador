<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Voice\Application\UseCases\CloneVoice;

use Helmreel\Shared\Shared\Domain\Services\Clock;
use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;
use Helmreel\Shared\Shared\Domain\ValueObjects\LocalPath;
use Helmreel\VideoProduction\Voice\Domain\Entities\Voice;
use Helmreel\VideoProduction\Voice\Domain\Repositories\VoiceRepository;
use Helmreel\VideoProduction\Voice\Domain\ValueObjects\VoiceId;
use Illuminate\Support\Str;

final readonly class CloneVoice
{
    public function __construct(
        private VoiceRepository $voiceRepository,
        private Clock $clock,
    ) {
    }

    public function execute(string $audioPath, string $name, int $userId): array
    {
        $platformId = $this->voiceRepository->clone($audioPath, $name);

        $voice = new Voice(
            id: new VoiceId(Str::uuid()->toString()),
            userId: new IntegerId($userId),
            name: $name,
            sourceAudioPath: new LocalPath($audioPath),
            createdAt: $this->clock->now(),
            platformId: $platformId,
        );

        $this->voiceRepository->save($voice);

        return $voice->toArray();
    }
}
