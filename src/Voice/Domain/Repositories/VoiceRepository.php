<?php

declare(strict_types=1);

namespace Canalizador\Voice\Domain\Repositories;

use Canalizador\Voice\Domain\Entities\Voice;
use Canalizador\Voice\Domain\ValueObjects\VoiceId;

interface VoiceRepository
{
    public function save(Voice $voice): void;

    public function findById(VoiceId $id): ?Voice;
}
