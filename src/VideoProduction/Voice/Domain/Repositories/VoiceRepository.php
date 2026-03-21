<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Voice\Domain\Repositories;

use Canalizador\VideoProduction\Voice\Domain\Entities\Voice;
use Canalizador\VideoProduction\Voice\Domain\ValueObjects\VoiceId;

interface VoiceRepository
{
    public function save(Voice $voice): void;

    public function findById(VoiceId $id): ?Voice;
}
