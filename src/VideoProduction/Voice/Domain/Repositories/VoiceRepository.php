<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Voice\Domain\Repositories;

use Helmreel\VideoProduction\Voice\Domain\Entities\Voice;
use Helmreel\VideoProduction\Voice\Domain\ValueObjects\VoiceId;

interface VoiceRepository
{
    public function save(Voice $voice): void;

    public function findById(VoiceId $id): ?Voice;
}
