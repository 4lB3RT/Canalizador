<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Voice\Application\UseCases\GetVoices;

use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;
use Helmreel\VideoProduction\Voice\Domain\Repositories\VoiceRepository;

final readonly class GetVoices
{
    public function __construct(
        private VoiceRepository $voiceRepository,
    ) {
    }

    public function execute(int $userId): array
    {
        $voices = $this->voiceRepository->findByUserId(new IntegerId($userId));

        return array_map(fn ($voice) => $voice->toArray(), $voices);
    }
}
