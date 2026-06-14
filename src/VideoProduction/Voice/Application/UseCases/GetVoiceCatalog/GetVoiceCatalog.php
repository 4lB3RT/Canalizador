<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Voice\Application\UseCases\GetVoiceCatalog;

use Helmreel\VideoProduction\Voice\Domain\Entities\Voice;
use Helmreel\VideoProduction\Voice\Domain\Repositories\VoiceRepository;

final readonly class GetVoiceCatalog
{
    public function __construct(
        private VoiceRepository $voiceRepository,
    ) {
    }

    public function execute(): array
    {
        return array_map(
            fn (Voice $voice) => $voice->toArray(),
            $this->voiceRepository->get()->items(),
        );
    }
}
